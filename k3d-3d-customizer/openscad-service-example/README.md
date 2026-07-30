# مثال خدمة OpenSCAD Adapter

ده مش جزء من إضافة الووردبريس - سكريبت Python منفصل تمامًا، لازم يتنشر
على سيرفر/VPS لوحده عنده OpenSCAD مثبّت (استضافة ووردبريس العادية مش
بتسمح بتشغيل برامج CLI زي OpenSCAD).

## الفكرة

إضافة **K3D 3D Customizer** بتبعت طلب POST لما عميل يأكد طلب فيه منتج
مخصص، بالشكل ده:

```json
{
  "template": "name_keychain",
  "parameters": {
    "text": "خالد",
    "font": "Lateef",
    "baseColor": "White",
    "textColor": "#D9583A",
    "border": 2.0
  }
}
```

والسكريبت ده بيستقبل الطلب، يشغّل OpenSCAD فعليًا على ملف `.scad` عندك
(بالقيم دي كـ`-D` variables)، وبيرجّع رابط ملف STL جاهز:

```json
{ "success": true, "files": { "stl": "http://your-server:5000/files/xxxx.stl" } }
```

## التركيب (على سيرفر Ubuntu مثلاً)

```bash
sudo apt update && sudo apt install -y openscad
pip install flask
```

حط ملفات `.scad` بتاعتك جوه مجلد `templates/` (مثلاً `templates/name_keychain.scad`
أو `templates/arabic_keychain_v4.scad` كافتراضي).

عدّل `TEMPLATE_VAR_MAP` في `app.py` لو أسماء المتغيرات جوه ملف الـscad
بتاعك مختلفة عن اللي في المثال (شوف أول الملف .scad نفسه، المتغيرات
بتبقى معرّفة في الأول زي `Arabic_Text = "..."`).

```bash
export K3D_WEBHOOK_SECRET="اختار-مفتاح-سري-قوي"
python app.py
```

للتشغيل الدائم (مش بس تجربة) استخدم `gunicorn`/`systemd`/Docker بدل
`python app.py` مباشرة، وحط السيرفر ده خلف HTTPS (Nginx reverse proxy
مثلاً) - الرابط اللي هتحطه في إعدادات الإضافة لازم يكون `https://`.

## ربطه بالإضافة

في لوحة تحكم ووردبريس: **K3D 3D Customizer > مولّد ملفات التصنيع**:
- رابط خدمة التوليد: `https://your-server/generate`
- المفتاح السري: نفس قيمة `K3D_WEBHOOK_SECRET`

وفي صفحة تعديل كل منتج مفعّل عليه المعاينة، حط "اسم القالب" (لازم يطابق
اسم ملف الـscad من غير الامتداد، مثلاً `name_keychain`).
