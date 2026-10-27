import Foundation
import ImageIO
import PDFKit
import Vision

struct TextBlock: Encodable {
    let text: String
    let confidence: Float
    let x: Double
    let y: Double
    let width: Double
    let height: Double
}

struct SourcePage: Encodable {
    let number: Int
    let blocks: [TextBlock]
}

struct SourceDocument: Encodable {
    let schemaVersion = "receipt-source/1"
    let engine = "apple-vision"
    let requiresReview = true
    let pages: [SourcePage]
}

enum ExtractionError: Error {
    case usage, invalidPDF, pageLimit, invalidPage, invalidSize, missingImage
}

func recognize(_ page: PDFPage) throws -> [TextBlock] {
    let bounds = page.bounds(for: .mediaBox)
    guard bounds.width > 0, bounds.height > 0,
          bounds.width.isFinite, bounds.height.isFinite else {
        throw ExtractionError.invalidSize
    }
    let scale = 3200 / max(bounds.width, bounds.height)
    let size = NSSize(width: bounds.width * scale, height: bounds.height * scale)
    let image = page.thumbnail(of: size, for: .mediaBox)
    guard let cgImage = image.cgImage(forProposedRect: nil, context: nil, hints: nil) else {
        throw ExtractionError.missingImage
    }
    return try recognizeImage(cgImage)
}

func recognizeImage(_ image: CGImage) throws -> [TextBlock] {
    guard image.width * image.height <= 24_000_000 else { throw ExtractionError.invalidSize }
    let request = VNRecognizeTextRequest()
    request.recognitionLevel = .accurate
    request.recognitionLanguages = ["es-ES", "en-US"]
    request.usesLanguageCorrection = false
    try VNImageRequestHandler(cgImage: image).perform([request])
    return (request.results ?? []).compactMap { observation in
        guard let candidate = observation.topCandidates(1).first else { return nil }
        let box = observation.boundingBox
        return TextBlock(text: candidate.string, confidence: candidate.confidence,
                         x: box.minX, y: 1 - box.maxY, width: box.width, height: box.height)
    }.sorted { $0.y == $1.y ? $0.x < $1.x : $0.y < $1.y }
}

func extract() throws -> Data {
    guard CommandLine.arguments.count == 2 else { throw ExtractionError.usage }
    let url = URL(fileURLWithPath: CommandLine.arguments[1])
    let attributes = try FileManager.default.attributesOfItem(atPath: url.path)
    guard let size = attributes[.size] as? NSNumber, size.intValue <= 20 * 1024 * 1024 else {
        throw ExtractionError.invalidPDF
    }
    var pages: [SourcePage] = []
    if let document = PDFDocument(url: url) {
        guard !document.isLocked else { throw ExtractionError.invalidPDF }
        guard (1...6).contains(document.pageCount) else { throw ExtractionError.pageLimit }
        for index in 0..<document.pageCount {
            let result = try autoreleasepool {
                guard let page = document.page(at: index) else { throw ExtractionError.invalidPage }
                return try SourcePage(number: index + 1, blocks: recognize(page))
            }
            pages.append(result)
        }
    } else {
        guard let source = CGImageSourceCreateWithURL(url as CFURL, nil),
              let properties = CGImageSourceCopyPropertiesAtIndex(source, 0, nil) as? [CFString: Any],
              let width = properties[kCGImagePropertyPixelWidth] as? Int,
              let height = properties[kCGImagePropertyPixelHeight] as? Int,
              width > 0, height > 0, width <= 24_000_000 / height,
              let image = CGImageSourceCreateImageAtIndex(source, 0, nil) else {
            throw ExtractionError.missingImage
        }
        pages.append(try SourcePage(number: 1, blocks: recognizeImage(image)))
    }
    let encoder = JSONEncoder()
    encoder.outputFormatting = [.prettyPrinted, .sortedKeys, .withoutEscapingSlashes]
    return try encoder.encode(SourceDocument(pages: pages))
}

do {
    FileHandle.standardOutput.write(try extract())
} catch {
    FileHandle.standardError.write(Data("OCR failed. Check input PDF, page limit and local Vision availability.\n".utf8))
    exit(1)
}
