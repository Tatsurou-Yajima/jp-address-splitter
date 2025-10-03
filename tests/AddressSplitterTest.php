<?php

declare(strict_types=1);

namespace YajimaTatsuro\JpAddressSplitter\Tests;

use PHPUnit\Framework\TestCase;
use YajimaTatsuro\JpAddressSplitter\AddressSplitter;

class AddressSplitterTest extends TestCase
{
    /**
     * @dataProvider addressProvider
     */
    public function test_正常系(
        string $address,
        ?string $expected_prefecture,
        ?string $expected_city,
        string $expected_rest
    ): void {
        $address_splitter = new AddressSplitter();
        $result = $address_splitter->split($address);

        $this->assertSame($expected_prefecture, $result['prefecture']);
        $this->assertSame($expected_city, $result['city']);
        $this->assertSame($expected_rest, $result['rest']);
    }

    public static function addressProvider(): array
    {
        return [
            '東京都渋谷区' => [
                '東京都渋谷区恵比寿1-1-1',
                '東京都',
                '渋谷区',
                '恵比寿1-1-1',
            ],
            '北海道札幌市中央区' => [
                '北海道札幌市中央区大通西1-1-1',
                '北海道',
                '札幌市中央区',
                '大通西1-1-1',
            ],
            '大阪府大阪市北区' => [
                '大阪府大阪市北区梅田1-1-1',
                '大阪府',
                '大阪市北区',
                '梅田1-1-1',
            ],
            '京都府京都市下京区' => [
                '京都府京都市下京区四条通1-1-1',
                '京都府',
                '京都市下京区',
                '四条通1-1-1',
            ],
            '都道府県なし'       => ['渋谷区恵比寿1-1-1', null, '渋谷区', '恵比寿1-1-1'],
            '空文字列'           => ['', null, null, ''],
            '空白のみ'           => ['   ', null, null, '   '],
            '市名に市が含まれる' => ['広島県廿日市市1-1-1', '広島県', '廿日市市', '1-1-1'],
            '郡+町'              => [
                '群馬県佐波郡玉村町1丁目2番地3',
                '群馬県',
                '佐波郡玉村町',
                '1丁目2番地3',
            ],
            '郡+村' => [
                '東京都西多摩郡奥多摩町1-1-1',
                '東京都',
                '西多摩郡奥多摩町',
                '1-1-1',
            ],
            '市名に村が含まれる' => [
                '東京都東村山市本町1丁目2番地3',
                '東京都',
                '東村山市',
                '本町1丁目2番地3',
            ],
        ];
    }

    /**
     * @dataProvider allPrefecturesProvider
     */
    public function testAllPrefectures(string $prefecture): void
    {
        $address_splitter = new AddressSplitter();
        $address = $prefecture . 'テスト市1-1-1';
        $result = $address_splitter->split($address);

        $this->assertSame($prefecture, $result['prefecture'], "Failed for prefecture: {$prefecture}");
    }

    /**
     * 意図的に失敗するテスト（CI検証用）
     */
    public function testIntentionallyFailingTest(): void
    {
        $this->assertSame('expected', 'actual', 'This test is designed to fail for CI verification');
    }

    public static function allPrefecturesProvider(): array
    {
        $prefectures = [
            '北海道',
            '青森県',
            '岩手県',
            '宮城県',
            '秋田県',
            '山形県',
            '福島県',
            '茨城県',
            '栃木県',
            '群馬県',
            '埼玉県',
            '千葉県',
            '東京都',
            '神奈川県',
            '新潟県',
            '富山県',
            '石川県',
            '福井県',
            '山梨県',
            '長野県',
            '岐阜県',
            '静岡県',
            '愛知県',
            '三重県',
            '滋賀県',
            '京都府',
            '大阪府',
            '兵庫県',
            '奈良県',
            '和歌山県',
            '鳥取県',
            '島根県',
            '岡山県',
            '広島県',
            '山口県',
            '徳島県',
            '香川県',
            '愛媛県',
            '高知県',
            '福岡県',
            '佐賀県',
            '長崎県',
            '熊本県',
            '大分県',
            '宮崎県',
            '鹿児島県',
            '沖縄県',
        ];

        return array_map(fn ($prefecture) => [$prefecture], $prefectures);
    }

    public function testValidateKenAllCsv(): void
    {
        $csv_file = 'KEN_ALL.CSV';

        $handle = fopen($csv_file, 'r');

        $all_success = true;
        $total_count = 0;
        $failures = [];

        while (($line = fgets($handle)) !== false) {
            $line = mb_convert_encoding($line, 'UTF-8', 'SJIS');

            $parts = str_getcsv($line);

            if (count($parts) < 8) {
                continue;
            }

            $prefecture = $parts[6] ?? null;
            $city = $parts[7] ?? null;
            $town = $parts[8] ?? null;

            if (!$prefecture || !$city) {
                continue;
            }

            $address = $prefecture . $city . $town;
            $total_count++;

            $address_splitter = new AddressSplitter();
            $result = $address_splitter->split($address);

            $prefecture_valid = $result['prefecture'] === $prefecture;
            $city_valid = $result['city'] === $city ||
                str_contains($result['city'] ?? '', $city) ||
                str_contains($city, $result['city'] ?? '');

            $is_success = $prefecture_valid && $city_valid;

            if (!$is_success) {
                $all_success = false;
                if (count($failures) < 10) {
                    $failures[] = [
                        'address'             => $address,
                        'expected_prefecture' => $prefecture,
                        'expected_city'       => $city,
                        'actual_prefecture'   => $result['prefecture'],
                        'actual_city'         => $result['city'],
                    ];
                }
            }
        }//end while

        fclose($handle);

        $this->assertTrue($all_success);

        if (!$all_success) {
            $failure_details = '';
            foreach ($failures as $failure) {
                $failure_details .= sprintf(
                    "住所: %s, 期待: 都道府県=%s, 市区町村=%s, 実際: 都道府県=%s, 市区町村=%s\n",
                    $failure['address'],
                    $failure['expected_prefecture'],
                    $failure['expected_city'],
                    $failure['actual_prefecture'],
                    $failure['actual_city']
                );
            }

            $this->fail("失敗ケース（最初の10件）:\n" . $failure_details);
        }
    }
}
