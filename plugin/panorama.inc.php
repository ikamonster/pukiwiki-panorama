<?php
/*
PukiWiki - Yet another WikiWikiWeb clone.
panorama.inc.php, v1.0.1 2020 M.Taniguchi
License: GPL v3 or (at your option) any later version

6枚のキューブマップ画像をパノラマ表示するプラグイン。

【使い方】
#panorama(frontImage,backImage,leftImage,rightImage,topImage,bottomImage[,[width][,[height][,[direction][,[autoRotSpeed][,[fov]]]]]])

frontImage   … キューブマップ前画像。添付ファイル名またはURL
backImage    … キューブマップ背画像。添付ファイル名またはURL
leftImage    … キューブマップ左画像。添付ファイル名またはURL
rightImage   … キューブマップ右画像。添付ファイル名またはURL
topImage     … キューブマップ上画像。添付ファイル名またはURL
bottomImage  … キューブマップ下画像。添付ファイル名またはURL
width        … 表示幅（px）。省略時の既定値は 640
height       … 表示高さ（px）。省略時の既定値は width * 9 / 16
direction    … 初期方向（度）。0～360。省略時の既定値は 0
autoRotSpeed … 自動回転速度。0なら無回転。省略時の既定値は 1
fov          … 画角（度）。30～120。省略時の既定値は 70

【使用例】
#panorama(front.jpg,back.jpg,left.jpg,right.jpg,top.jpg,bottom.jpg)
#panorama(front.jpg,back.jpg,left.jpg,right.jpg,top.jpg,bottom.jpg,480,360,-30,-1,100)
*/

/////////////////////////////////////////////////
// パノラマ画像表示プラグイン設定（panorama.inc.php）
if (!defined('PLUGIN_PANORAMA_WIDTH'))       define('PLUGIN_PANORAMA_WIDTH',     640); // 既定の表示幅（px）
if (!defined('PLUGIN_PANORAMA_ASPECT'))      define('PLUGIN_PANORAMA_ASPECT', (9/16)); // 既定の表示縦横比
if (!defined('PLUGIN_PANORAMA_FOV'))         define('PLUGIN_PANORAMA_FOV',        70); // 既定の画角（度）
if (!defined('PLUGIN_PANORAMA_AUTO_RESUME')) define('PLUGIN_PANORAMA_AUTO_RESUME', 5); // ドラッグ操作から自動回転への復帰時間（秒）


function plugin_panorama_convert() {
	global	$vars;
	static $included = 0;

	list($image[0], $image[1], $image[2], $image[3], $image[4], $image[5], $w, $h, $rot, $auto, $fov) = func_get_args();

	for ($i = 0; $i < 6; $i++) {
		$img = trim($image[$i]);
		if ($img) {
			if (preg_match('/(https?\:|\/)/i', $img)) {
				$image[$i] = htmlsc($img);
			} else {
				$image[$i] = '?plugin=attach&refer=' . rawurlencode($vars['page']) .'&openfile=' . rawurlencode($img);
			}
		} else {
			return '#panorama(frontImage,backImage,leftImage,rightImage,topImage,bottomImage[,[width][,[height][,[direction][,[autoRotSpeed][,[fov]]]]]])';
		}
	}

	$w = ($w)? (int)$w : PLUGIN_PANORAMA_WIDTH;
	$h = ($h)? (int)$h : $w * PLUGIN_PANORAMA_ASPECT;
	$rot = (float)$rot;
	$fov = ($fov)? max(30, min(120, (float)$fov)) : PLUGIN_PANORAMA_FOV;
	$auto = ($auto || $auto === '0')? (float)$auto : 1;
	$autoResume = 1000 * PLUGIN_PANORAMA_AUTO_RESUME;
	$size = min(1600, (($w >= $h)? $w : $h) * 1.5);
	$hsize = $size * 0.5;

	$body = '';
	if (!$included) {
		$body .= <<< EOT
<style>
._p_panorama {
	position: relative;
	top: 0;
	left: 0;
	z-index: 0;
	background: #808080;
	perspective-origin: center center;
	padding: 0;
	overflow: hidden;
	user-select:none; -moz-user-select:none; -webkit-user-select:none; -ms-user-select:none;
}

._p_panorama_pad {
	display: block;
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	margin: 0;
	padding: 0;
	border: none;
	background: transparent;
	z-index: 1;
	cursor: grab;
}
._p_panorama_cube {
	display: block;
	position: absolute;
	top: 0;
	left: 0;
	margin: 0;
	padding: 0;
	transform-style: preserve-3d;
	pointer-events: none;
}

._p_panorama_cube > * {
	display: block;
	position: absolute;
	top: 0;
	left: 0;
	margin: 0;
	padding: 0;
	text-align: center;
	vertical-align: middle;
	transform-style: preserve-3d;
	border: none;
	box-sizing: border-box;
	background-size: cover;
	background-repeat: no-repeat;
	-webkit-backface-visibility: hidden;
	backface-visibility: hidden;
}
</style>

<script>
"use strict";var __PluginPanorama__=function(t,e,a,n,o,i,s){const d=this;this.id=t,this.maxW=e,this.maxH=a,this.w=0,this.h=0,this.fov=n,this.perspective=521,this.rotY=this.rotY2=o,this.rotX=this.rotX2=0,this.auto=i,this.container=document.getElementById("_p_panorama-"+this.id),this.pad=document.getElementById("_p_panorama_pad-"+this.id),this.cube=document.getElementById("_p_panorama_cube-"+this.id),this.padStartX=0,this.padStartY=0,this.padEndX=0,this.padEndY=0,this.padOn=!1,this.time=0,this.autoResume=0,this.autoResumeTime=s,d.onResize(),d.pad.addEventListener("mousedown",function(t){d.onMove(t,!0)},{passive:!1}),d.pad.addEventListener("touchstart",function(t){d.onMove(t,!0)},{passive:!1}),d.pad.addEventListener("mousemove",function(t){d.onMove(t,!1)},{passive:!1}),d.pad.addEventListener("touchmove",function(t){d.onMove(t,!1)},{passive:!1}),d.pad.addEventListener("mouseup",function(t){d.onMoveEnd(t)},{passive:!0}),d.pad.addEventListener("touchend",function(t){d.onMoveEnd(t)},{passive:!0}),d.pad.addEventListener("mouseout",function(t){d.onMoveEnd(t)},{passive:!0}),d.pad.addEventListener("mouseleave",function(t){d.onMoveEnd(t)},{passive:!0}),d.pad.addEventListener("touchleave",function(t){d.onMoveEnd(t)},{passive:!0}),d.pad.addEventListener("touchcancel",function(t){d.onMoveEnd(t)},{passive:!0}),window.addEventListener("resize",function(t){d.onResize(t)}),window.addEventListener("load",function(){requestAnimationFrame(function(t){d.Update(t)})})};__PluginPanorama__.prototype.Update=function(t){const e=this;e.time||(e.time=t-1e3/60);var a=Math.min(t-e.time,100);e.time=t,!e.padOn&&e.auto&&(e.autoResume-=a,e.autoResume<=0&&(e.rotY+=360*e.auto/60*a/1e3,e.rotX-=e.rotX*a/1e4)),e.rotY2+=(e.rotY-e.rotY2)*a/50,e.rotX2+=(e.rotX-e.rotX2)*a/50,e.cube.style.transform="translate3d("+.5*e.w+"px,"+.5*e.h+"px,"+e.perspective+"px) rotateX("+e.rotX2+"deg) rotateY("+(e.rotY2+180)+"deg)",requestAnimationFrame(function(t){e.Update(t)})},__PluginPanorama__.prototype.onResize=function(t){const e=this;var a=Math.min(e.maxW,e.container.clientWidth);e.w!=a&&(e.w=Math.min(e.maxW,e.container.clientWidth),e.h=e.w*e.maxH/e.maxW,e.perspective=Math.pow(.5*e.w*(.5*e.w)+.5*e.h*(.5*e.h),.5)/Math.tan(e.fov*Math.PI/360),e.container.style.perspective=e.perspective+"px",e.container.style.maxWidth=e.maxW+"px",e.container.style.maxHeight=e.maxH+"px",e.container.style.width="100%",e.container.style.height=e.h+"px",e.cube.style.transform="translate3d("+.5*e.w+"px,"+.5*e.h+"px,"+e.perspective+"px) rotateY("+(e.rotY2+180)+"deg)")},__PluginPanorama__.prototype.onMoveEnd=function(t){this.padOn=!1,this.pad.style.cursor="grab"},__PluginPanorama__.prototype.onMove=function(t,e){const a=this;if(t.preventDefault(),e)a.padStartX=t.pageX,a.padStartY=t.pageY,void 0!==t.originalEvent&&void 0!==t.originalEvent.touches&&(a.padStartX=t.originalEvent.touches[0].pageX,a.padStartY=t.originalEvent.touches[0].pageY),a.padOn=!0,a.pad.style.cursor="grabbing",a.autoResume=a.autoResumeTime;else if(a.padOn){a.padEndX=t.pageX,a.padEndY=t.pageY,void 0!==t.originalEvent&&void 0!==t.originalEvent.touches&&(a.padEndX=t.originalEvent.touches[0].pageX,a.padEndY=t.originalEvent.touches[0].pageY);var n=a.padEndX-a.padStartX,o=a.padEndY-a.padStartY;a.padStartX=a.padEndX,a.padStartY=a.padEndY,a.rotY-=.25*n,a.rotX+=.25*o,a.rotX<-89?a.rotX=-89:a.rotX>89&&(a.rotX=89),a.autoResume=a.autoResumeTime}};
</script>
EOT;
	}

	$body .= <<< EOT
<div class="_p_panorama" id="_p_panorama-{$included}">
	<div class="_p_panorama_cube" id="_p_panorama_cube-{$included}">
		<div class="_p_panorama_front"  style="width:{$size}px;height:{$size}px;transform:translate3d(-{$hsize}px,-{$hsize}px,{$hsize}px) rotateY(180deg);background-image:url('{$image[0]}')"></div>
		<div class="_p_panorama_back"   style="width:{$size}px;height:{$size}px;transform:translate3d(-{$hsize}px,-{$hsize}px,-{$hsize}px);background-image:url('{$image[1]}');"></div>
		<div class="_p_panorama_left"   style="width:{$size}px;height:{$size}px;transform:translate3d(-{$size}px,-{$hsize}px,0) rotateY(90deg);background-image:url('{$image[3]}')"></div>
		<div class="_p_panorama_right"  style="width:{$size}px;height:{$size}px;transform:translate3d(0,-{$hsize}px,0) rotateY(-90deg);background-image:url('{$image[2]}')"></div>
		<div class="_p_panorama_top"    style="width:{$size}px;height:{$size}px;transform:translate3d(-{$hsize}px,-{$size}px,0) rotateX(90deg) rotateY(180deg);background-image:url('{$image[4]}')"></div>
		<div class="_p_panorama_bottom" style="width:{$size}px;height:{$size}px;transform:translate3d(-{$hsize}px,0,0) rotateX(-90deg) rotateY(180deg);background-image:url('{$image[5]}')"></div>
	</div>
	<div class="_p_panorama_pad" id="_p_panorama_pad-{$included}"></div>
</div>
<script>
new __PluginPanorama__({$included}, {$w}, {$h}, {$fov}, {$rot}, {$auto}, {$autoResume});
</script>
EOT;

	$included++;

	return $body;
}
