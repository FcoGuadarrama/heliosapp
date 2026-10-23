"""Build area weights for the native NASA 1-degree grid from INEGI polygons."""

import gzip
import hashlib
import json
import math
from pathlib import Path

import pyproj
import shapely
from pyproj import Transformer
from shapely.geometry import box, shape
from shapely.ops import transform

ROOT = Path(__file__).resolve().parents[2]
SOURCE = ROOT / "resources/data/hsp/inegi-states-2025.geojson.gz"
OUTPUT = ROOT / "resources/data/hsp/state-grid-v1.json"


def build():
    raw = gzip.decompress(SOURCE.read_bytes())
    document = json.loads(raw)
    features = sorted(document["features"], key=lambda f: f["properties"]["cvegeo"])
    if [f["properties"]["cvegeo"] for f in features] != [
        f"{i:02d}" for i in range(1, 33)
    ]:
        raise ValueError("Expected the 32 INEGI state codes exactly once")
    project = Transformer.from_crs("EPSG:4326", "EPSG:6933", always_xy=True).transform
    states = []
    for feature in features:
        polygon = shape(feature["geometry"])
        if polygon.is_empty or not polygon.is_valid:
            raise ValueError(
                "Invalid INEGI geometry: " + feature["properties"]["cvegeo"]
            )
        xmin, ymin, xmax, ymax = polygon.bounds
        cells = []
        for x in range(math.floor(xmin), math.ceil(xmax)):
            for y in range(math.floor(ymin), math.ceil(ymax)):
                clipped = polygon.intersection(box(x, y, x + 1, y + 1))
                area = transform(project, clipped).area
                if area > 0:
                    cells.append(
                        {"longitude": x + 0.5, "latitude": y + 0.5, "area_m2": area}
                    )
        total = sum(c["area_m2"] for c in cells)
        reference_area = transform(project, polygon).area
        relative_error = abs(total - reference_area) / reference_area
        if relative_error > 0.0001:
            raise ValueError("Intersection area does not conserve state coverage")
        for cell in cells:
            cell["weight"] = round(cell.pop("area_m2") / total, 12)
        states.append(
            {
                "code": feature["properties"]["cvegeo"],
                "name": feature["properties"]["nomgeo"],
                "area_m2": round(total, 3),
                "area_partition_relative_error": relative_error,
                "cells": cells,
            }
        )
    result = {
        "methodology": "inegi-2025-ceres-1deg-area-v1",
        "source_url": "https://gaia.inegi.org.mx/wscatgeo/v2/geo/mgee/",
        "source_sha256": hashlib.sha256(raw).hexdigest(),
        "source_archive_sha256": hashlib.sha256(SOURCE.read_bytes()).hexdigest(),
        "source_metadata": document["metadatos"],
        "grid_degrees": 1,
        "area_crs": "EPSG:6933",
        "tool_versions": {"shapely": shapely.__version__, "pyproj": pyproj.__version__},
        "states": states,
    }
    OUTPUT.write_text(json.dumps(result, ensure_ascii=False, indent=4) + "\n")
    unique = {(c["longitude"], c["latitude"]) for s in states for c in s["cells"]}
    print(
        f"{len(states)} states; {len(unique)} unique NASA cells; {sum(len(s['cells']) for s in states)} intersections"
    )


if __name__ == "__main__":
    build()
