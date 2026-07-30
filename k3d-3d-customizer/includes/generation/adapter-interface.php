<?php
/**
 * العقد اللي أي "File Generation Adapter" لازم يلتزم بيه (OpenSCAD، أو أي
 * حاجة تانية مستقبلًا زي Blender/FreeCAD). Generation Manager (manager.php)
 * بيتعامل مع أي أداتر عن طريق الواجهة دي بس - من غير ما يعرف تفاصيل
 * تنفيذها، فاستبدال أداتر بأداتر تاني مايأثرش على باقي النظام.
 *
 * @package K3D_3D_Customizer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface K3D_3DC_Generation_Adapter {
	/**
	 * @param array{template:string, parameters:array<string,mixed>} $design "Design Object" موحّد - نفس الشكل بغض النظر عن نوع المنتج أو تصميم المعاينة.
	 * @return array{success:bool, files?:array<string,string>, errorCode?:string, message?:string}
	 */
	public function generate( array $design ): array;
}
