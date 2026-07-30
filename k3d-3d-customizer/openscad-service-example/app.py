"""
مثال تنفيذ حقيقي لخدمة "OpenSCAD Adapter" - سيرفر Flask بسيط بيستقبل
Design Object من إضافة K3D 3D Customizer (الووردبريس)، بيشغّل OpenSCAD
فعليًا لتوليد ملف STL، وبيرجّع رابط الملف الجاهز.

مهم: الملف ده منفصل تمامًا عن الووردبريس - استضافة ووردبريس العادية
مش بتسمح بتشغيل OpenSCAD (محتاج CLI binary مثبّت على السيرفر). لازم
تنشر السكريبت ده على أي سيرفر/VPS عندك وصول SSH ليه وتقدر تثبّت عليه
OpenSCAD (https://openscad.org/downloads.html).

التشغيل محليًا للتجربة:
    pip install flask
    python app.py

بعد كده حط رابط السيرفر في K3D 3D Customizer > الإعدادات (لوحة تحكم
ووردبريس) في حقل "رابط خدمة التوليد":
    http://your-server:5000/generate
"""

import os
import subprocess
import uuid
from pathlib import Path

from flask import Flask, jsonify, request, send_from_directory

app = Flask(__name__)

BASE_DIR = Path(__file__).parent
TEMPLATES_DIR = BASE_DIR / "templates"
OUTPUT_DIR = BASE_DIR / "output"
OUTPUT_DIR.mkdir(exist_ok=True)

OPENSCAD_BIN = os.environ.get("OPENSCAD_BIN", "openscad")
SECRET = os.environ.get("K3D_WEBHOOK_SECRET", "")
DEFAULT_TEMPLATE_FILE = "arabic_keychain_v4.scad"

# اسم متغير OpenSCAD (جوه ملف .scad) المقابل لكل حقل في الـDesign Object
# اللي الإضافة بتبعته. القيم هنا مأخوذة من ملف arabic_keychain_v4.scad -
# لو عندك قالب تاني بأسماء متغيرات مختلفة، ضيفه هنا بمفتاحه (template).
TEMPLATE_VAR_MAP = {
    "name_keychain": {
        "text": "Arabic_Text",
        "font": "Font_Name",
        "baseColor": "Layer1_Color",
        "textColor": "Layer2_Color",
        "border": "Border_Thickness_mm",
    },
}


@app.post("/generate")
def generate():
    if SECRET and request.headers.get("X-K3D-Secret") != SECRET:
        return jsonify(success=False, errorCode="UNAUTHORIZED", message="Bad secret"), 401

    data = request.get_json(silent=True) or {}
    template = data.get("template") or "name_keychain"
    params = data.get("parameters") or {}

    scad_file = TEMPLATES_DIR / f"{template}.scad"
    if not scad_file.exists():
        scad_file = TEMPLATES_DIR / DEFAULT_TEMPLATE_FILE

    if not scad_file.exists():
        return (
            jsonify(
                success=False,
                errorCode="TEMPLATE_NOT_FOUND",
                message=f'حط ملف "{DEFAULT_TEMPLATE_FILE}" (أو قالبك) جوه مجلد templates/.',
            ),
            404,
        )

    var_map = TEMPLATE_VAR_MAP.get(template, TEMPLATE_VAR_MAP["name_keychain"])
    job_id = uuid.uuid4().hex[:12]
    stl_path = OUTPUT_DIR / f"{job_id}.stl"

    cmd = [OPENSCAD_BIN, "-o", str(stl_path)]
    for key, value in params.items():
        scad_var = var_map.get(key)
        if not scad_var or value in (None, ""):
            continue
        formatted = f"{value}" if _is_number(value) else f'"{value}"'
        cmd += ["-D", f"{scad_var}={formatted}"]
    cmd.append(str(scad_file))

    try:
        subprocess.run(cmd, check=True, capture_output=True, timeout=120)
    except subprocess.CalledProcessError as exc:
        return (
            jsonify(
                success=False,
                errorCode="SCAD_GENERATION_FAILED",
                message=exc.stderr.decode("utf-8", "ignore")[:500],
            ),
            500,
        )
    except subprocess.TimeoutExpired:
        return jsonify(success=False, errorCode="SCAD_TIMEOUT", message="انتهت مهلة توليد الملف."), 504

    file_url = request.host_url.rstrip("/") + f"/files/{stl_path.name}"
    return jsonify(success=True, files={"stl": file_url})


@app.get("/files/<path:filename>")
def files(filename):
    return send_from_directory(OUTPUT_DIR, filename)


def _is_number(value) -> bool:
    return isinstance(value, (int, float)) and not isinstance(value, bool)


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)
