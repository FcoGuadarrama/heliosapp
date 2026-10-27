"""Generate small, deterministic PDF fixtures without customer information."""

from pathlib import Path


def pdf(lines: list[str]) -> bytes:
    escaped = [
        line.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")
        for line in lines
    ]
    stream = (
        "BT /F1 14 Tf 45 770 Td 26 TL "
        + " Tj T* ".join(f"({line})" for line in escaped)
        + " Tj ET"
        if lines
        else ""
    )
    objects = [
        "<< /Type /Catalog /Pages 2 0 R >>",
        "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
        (
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 842] "
            "/Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>"
        ),
        "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        f"<< /Length {len(stream)} >>\nstream\n{stream}\nendstream",
    ]
    data = b"%PDF-1.4\n"
    offsets = []
    for index, item in enumerate(objects, 1):
        offsets.append(len(data))
        data += f"{index} 0 obj\n{item}\nendobj\n".encode("ascii")
    xref = len(data)
    data += f"xref\n0 {len(objects) + 1}\n0000000000 65535 f \n".encode()
    for offset in offsets:
        data += f"{offset:010d} 00000 n \n".encode()
    data += (
        f"trailer << /Size {len(objects) + 1} /Root 1 0 R >>\n"
        f"startxref\n{xref}\n%%EOF\n"
    ).encode()
    return data


def main() -> None:
    destination = Path(__file__).resolve().parents[2] / "resources/fixtures/receipts"
    header = "CFE - MUESTRA SINTETICA - NO VALIDA PARA PAGO"
    fixtures = {
        "cfe-source": [
            header,
            "TARIFA: 1C NO. MEDIDOR: TEST",
            "NO HILOS: 2",
            "PERIODO FACTURADO: 15 JUL 26 - 13 SEP 26",
            "Energia (kWh) 1,520 1,000 520",
            "del 15 MAY 26 al 15 JUL 26 480 $1200.00",
        ],
        "readable": [
            header,
            "Tarifa: 1C",
            "Periodo del 2026-07-15 al 2026-09-13",
            "Dias: 60",
            "Consumo: 520 kWh",
            "Total: 1234.56 MXN",
            "Domicilio de suministro:",
            "Calle de Prueba 123, Centro",
            "Queretaro, Queretaro, CP 76000",
            "Historico de consumo:",
            "2026-03-15 al 2026-05-14: 450 kWh, 60 dias",
            "2026-05-15 al 2026-07-14: 480 kWh, 60 dias",
        ],
        "partial": [header, "Tarifa: 1C", "Numero de servicio: 000012345678"],
        "unreadable": [],
    }
    for name, lines in fixtures.items():
        (destination / f"synthetic-{name}.pdf").write_bytes(pdf(lines))


if __name__ == "__main__":
    main()
