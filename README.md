# PukiWiki用プラグイン<br>パノラマ画像表示 panorama.inc.php

6枚組のキューブマップ画像をパノラマ表示する[PukiWiki](https://pukiwiki.osdn.jp/)用プラグイン。

|対象PukiWikiバージョン|対象PHPバージョン|
|:---:|:---:|
|PukiWiki 1.5.3 ~ 1.5.4RC (UTF-8)|PHP 7.4 ~ 8.1|

## インストール

下記GitHubページからダウンロードした panorama.inc.php を PukiWiki の plugin ディレクトリに配置してください。

[https://github.com/ikamonster/pukiwiki-panorama](https://github.com/ikamonster/pukiwiki-panorama)

## 使い方

```
#panorama(frontImage,backImage,leftImage,rightImage,topImage,bottomImage[,[width][,[height][,[direction][,[autoRotSpeed][,[fov]]]]]])
```

* frontImage … キューブマップ前画像。添付ファイル名またはURL
* backImage … キューブマップ背画像。添付ファイル名またはURL
* leftImage … キューブマップ左画像。添付ファイル名またはURL
* rightImage … キューブマップ右画像。添付ファイル名またはURL
* topImage … キューブマップ上画像。添付ファイル名またはURL
* bottomImage … キューブマップ下画像。添付ファイル名またはURL
* width … 表示幅（px）。省略時の既定値は 640
* height … 表示高さ（px）。省略時の既定値は width * 9 / 16
* direction … 初期方向（度）。0～360。省略時の既定値は 0
* autoRotSpeed … 自動回転速度。0なら無回転。省略時の既定値は 1
* fov … 画角（度）。30～120。省略時の既定値は 70

## 使用例

```
#panorama(front.jpg,back.jpg,left.jpg,right.jpg,top.jpg,bottom.jpg)
#panorama(front.jpg,back.jpg,left.jpg,right.jpg,top.jpg,bottom.jpg,480,360,-30,-1,100)
```

## 設定

ソース内の下記の定数で動作を制御することができます。

|定数名|値|既定値|意味|
|:---|:---:|:---:|:---|
|PLUGIN_PANORAMA_WIDTH|数値|640|既定の表示幅（px）|
|PLUGIN_PANORAMA_ASPECT|数値|(9 / 16)|既定の表示縦横比|
|PLUGIN_PANORAMA_FOV|数値|70|既定の画角（度）|
|PLUGIN_PANORAMA_AUTO_RESUME|数値|5|ドラッグ操作から自動回転への復帰時間（秒）|
