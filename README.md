# JP Address Splitter

日本の住所文字列を都道府県、市区町村、それ以降に分割するPHPパッケージです。

> **Note for English speakers**: This package is designed for Japanese native speakers, so the README is written in Japanese. If you need English documentation, please use browser translation features or other translation tools.

## インストール

```bash
composer require yajima-tatsuro/jp-address-splitter
```

## 使い方

```php
use YajimaTatsuro\JpAddressSplitter\AddressSplitter;

$splitter = new AddressSplitter();

// 都道府県、市区町村、それ以降に分割
$result = $splitter->split('東京都渋谷区恵比寿1-1-1');
// 結果: ['prefecture' => '東京都', 'city' => '渋谷区', 'rest' => '恵比寿1-1-1']
```

### 使用例

```php
use YajimaTatsuro\JpAddressSplitter\AddressSplitter;

$splitter = new AddressSplitter();
$address = '東京都渋谷区恵比寿1-1-1';
$result = $splitter->split($address);

// 個別の値にアクセス
$prefecture = $result['prefecture']; // '東京都'
$city = $result['city'];             // '渋谷区'
$rest = $result['rest'];             // '恵比寿1-1-1'

// 別の住所の例
$address2 = '北海道札幌市中央区大通西1-1-1';
$result2 = $splitter->split($address2);
// 結果: ['prefecture' => '北海道', 'city' => '札幌市中央区', 'rest' => '大通西1-1-1']

$address3 = '広島県廿日市市新宮1-1-1';
$result3 = $splitter->split($address3);
// 結果: ['prefecture' => '広島県', 'city' => '廿日市市', 'rest' => '新宮1-1-1']

$address4 = '群馬県佐波郡玉村町板井1-1-1';
$result4 = $splitter->split($address4);
// 結果: ['prefecture' => '群馬県', 'city' => '佐波郡玉村町', 'rest' => '板井1-1-1']
```

### 対応している市区町村

正規表現による柔軟な対応で、すべての市区町村を自動検出します：

- **区**: 東京都の特別区（例：渋谷区、足立区など）
- **市**: 一般的な市（例：川崎市、相模原市など）
- **町**: 市区町村の町（例：牟礼町、奥多摩町など）
- **村**: 村（例：檜原村など）
- **郡**: 郡部の町村（例：西多摩郡奥多摩町など）
- **市+区**: 市と区の組み合わせ（例：京都市東山区、大阪市北区など）

### 特殊なケースの対応

市名に「村」や「市」が含まれるケースも正しく処理できます：

```php
// 市名に「村」が含まれるケース
$result = $splitter->split('東村山市');
// 結果: ['prefecture' => null, 'city' => '東村山市', 'rest' => ''] （正しく動作）

$result = $splitter->split('東村山市本町1丁目2番地3');
// 結果: ['prefecture' => '東京都', 'city' => '東村山市', 'rest' => '本町1丁目2番地3'] （正しく動作）

// 市名に「市」が含まれるケース
$result = $splitter->split('廿日市市');
// 結果: ['prefecture' => null, 'city' => '廿日市市', 'rest' => ''] （正しく動作）

// 郡+町村のケース
$result = $splitter->split('佐波郡玉村町');
// 結果: ['prefecture' => null, 'city' => '佐波郡玉村町', 'rest' => ''] （正しく動作）

$result = $splitter->split('西多摩郡奥多摩町');
// 結果: ['prefecture' => null, 'city' => '西多摩郡奥多摩町', 'rest' => ''] （正しく動作）
```

## データソース

このパッケージでは、日本郵政HPに掲載されている住所データを元に分割処理を行います。
もし想定通り分割されない住所などありましたら、イシューにてご連絡ください！

## テスト

```bash
composer test
```

## ライセンス

MIT License

## 開発者

yajima-tatsuro
