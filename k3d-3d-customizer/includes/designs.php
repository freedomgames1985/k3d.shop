<?php
/**
 * سجل أنواع التصاميم القابلة للاختيار لكل منتج - كل تصميم = محرك عرض
 * JS مستقل (اسم موديول JS يسجل نفسه في K3DCustomizer.registerDesign)
 * + وصف بسيط (لوحة الألوان، أقصى عدد حروف، نوع المدخل) بيستخدمه PHP
 * لرسم فورم التخصيص من غير ما يعرف تفاصيل عرض الـ3D.
 *
 * لإضافة تصميم جديد مستقبلًا من غير ما تلمس هذا الملف: اعمل ملف JS في
 * assets/js/designs/، سجّله بـ wp_enqueue_script، وضيفه عن طريق
 * add_filter('k3d_3dc_designs', ...).
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, array{
 *   label:string, description:string, js_module:string, default_value:string,
 *   value_label:string, max_length:int, charset:string, colors:array<int,array{id:string,hex:string,label:string}>
 * }>
 */
function k3d_3dc_get_designs(): array {
	static $designs = null;

	if ( null !== $designs ) {
		return $designs;
	}

	$designs = apply_filters( 'k3d_3dc_designs', [
		'name' => [
			'label'         => __( 'اسم مجسم (ميدالية/سلسلة مفاتيح بالاسم)', 'k3d-3d-customizer' ),
			'description'   => __( 'العميل بيكتب اسمه وبيتحول لمجسم 3D فوري، ويختار اللون.', 'k3d-3d-customizer' ),
			'js_module'     => 'name',
			'default_value' => __( 'اسمك هنا', 'k3d-3d-customizer' ),
			'value_label'   => __( 'الاسم أو النص', 'k3d-3d-customizer' ),
			'max_length'    => 14,
			'charset'       => 'text',
			'colors'        => [
				[ 'id' => 'clay', 'hex' => '#D9583A', 'label' => __( 'طوبي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'ink', 'hex' => '#0E2024', 'label' => __( 'كحلي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'gold', 'hex' => '#B8862F', 'label' => __( 'ذهبي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'white', 'hex' => '#F3F5F1', 'label' => __( 'أبيض', 'k3d-3d-customizer' ) ],
				[ 'id' => 'rose', 'hex' => '#E07A9E', 'label' => __( 'وردي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'teal', 'hex' => '#2E8B8B', 'label' => __( 'فيروزي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'purple', 'hex' => '#7C5CBF', 'label' => __( 'بنفسجي', 'k3d-3d-customizer' ) ],
			],
		],
		'name_layers' => [
			'label'         => __( 'اسم بثلاث طبقات ملونة (زي القص متعدد الألوان)', 'k3d-3d-customizer' ),
			'description'   => __( 'العميل بيكتب اسمه وبيتحول لمجسم بحد ملوّن حوالين الحروف - تأثير الطبقات الملونة زي القطع الحقيقية.', 'k3d-3d-customizer' ),
			'js_module'     => 'name_layers',
			'default_value' => __( 'اسمك هنا', 'k3d-3d-customizer' ),
			'value_label'   => __( 'الاسم أو النص', 'k3d-3d-customizer' ),
			'max_length'    => 14,
			'charset'       => 'text',
			'colors'        => [
				[ 'id' => 'blue', 'hex' => '#38B3FF', 'label' => __( 'تركواز', 'k3d-3d-customizer' ) ],
				[ 'id' => 'clay', 'hex' => '#D9583A', 'label' => __( 'طوبي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'gold', 'hex' => '#B8862F', 'label' => __( 'ذهبي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'rose', 'hex' => '#E07A9E', 'label' => __( 'وردي', 'k3d-3d-customizer' ) ],
				[ 'id' => 'green', 'hex' => '#2E8434', 'label' => __( 'أخضر', 'k3d-3d-customizer' ) ],
				[ 'id' => 'purple', 'hex' => '#7C5CBF', 'label' => __( 'بنفسجي', 'k3d-3d-customizer' ) ],
			],
		],
		'plate' => [
			'label'         => __( 'لوحة/رقم (أرقام فقط - ميدالية سيارة أو رقم هاتف)', 'k3d-3d-customizer' ),
			'description'   => __( 'العميل بيدخل رقم (لوحة سيارة أو تليفون) وبيتحول للوحة 3D مجسمة.', 'k3d-3d-customizer' ),
			'js_module'     => 'plate',
			'default_value' => '12-345-67',
			'value_label'   => __( 'الرقم', 'k3d-3d-customizer' ),
			'max_length'    => 12,
			'charset'       => 'digits-dash',
			'colors'        => [
				[ 'id' => 'yellow', 'hex' => '#F5C518', 'label' => __( 'رجالة (أصفر)', 'k3d-3d-customizer' ) ],
				[ 'id' => 'white', 'hex' => '#F3F5F1', 'label' => __( 'خصوصي (أبيض)', 'k3d-3d-customizer' ) ],
				[ 'id' => 'black', 'hex' => '#16211f', 'label' => __( 'عسكري (أسود)', 'k3d-3d-customizer' ) ],
				[ 'id' => 'red', 'hex' => '#C0392B', 'label' => __( 'شرطة (أحمر)', 'k3d-3d-customizer' ) ],
			],
		],
		'car_plate' => [
			'label'         => __( 'لوحة سيارة إسرائيلية (إطار + شريط IL + رقم بارز)', 'k3d-3d-customizer' ),
			'description'   => __( 'تقليد حقيقي للوحة سيارة إسرائيلية - إطار غامق، شريط "IL" أزرق، ثقب تعليق حقيقي، والرقم منقوش بارز. فيه خانة اسم/نص إضافي اختيارية تحت الرقم.', 'k3d-3d-customizer' ),
			'js_module'     => 'car_plate',
			'default_value' => '123-45-678',
			'value_label'   => __( 'رقم اللوحة', 'k3d-3d-customizer' ),
			'max_length'    => 10,
			'charset'       => 'digits-dash',
			'colors'        => [
				[ 'id' => 'yellow', 'hex' => '#F5C518', 'label' => __( 'رجالة (أصفر)', 'k3d-3d-customizer' ) ],
				[ 'id' => 'white', 'hex' => '#F3F5F1', 'label' => __( 'خصوصي (أبيض)', 'k3d-3d-customizer' ) ],
				[ 'id' => 'black', 'hex' => '#16211f', 'label' => __( 'عسكري (أسود)', 'k3d-3d-customizer' ) ],
				[ 'id' => 'red', 'hex' => '#C0392B', 'label' => __( 'شرطة (أحمر)', 'k3d-3d-customizer' ) ],
			],
			// خانة تانية اختيارية - أي تصميم يقدر يضيفها بنفس الطريقة عشان
			// يظهر له حقل إدخال تاني تلقائيًا (init.js وproduct-render.php
			// بيقروا المفتاح ده من غير ما يعرفوا حاجة عن car_plate تحديدًا).
			'secondary'     => [
				'label'       => __( 'اسم أو نص إضافي (اختياري)', 'k3d-3d-customizer' ),
				'placeholder' => __( 'اسم إضافي تحت الرقم...', 'k3d-3d-customizer' ),
				'max_length'  => 16,
			],
		],
	] );

	return $designs;
}

function k3d_3dc_get_design( string $key ): ?array {
	$designs = k3d_3dc_get_designs();

	return $designs[ $key ] ?? null;
}
