<?php

declare(strict_types=1);

namespace YajimaTatsuro\JpAddressSplitter;

class AddressSplitter
{
    public function __construct(
        private readonly array $prefectures = [
            '東京都', '神奈川県', '大阪府', '京都府', '北海道', '青森県', '岩手県',
            '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県',
            '埼玉県', '千葉県', '新潟県', '富山県', '石川県', '福井県', '山梨県',
            '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '兵庫県',
            '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
            '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
            '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県',
        ]
    ) {
    }

    public function split(string $address): array
    {
        if (trim($address) === '') {
            return ['prefecture' => null, 'city' => null, 'rest' => $address];
        }

        $trimmed_address = trim($address);

        $prefecture = null;
        $rest_after_prefecture = $trimmed_address;

        foreach ($this->prefectures as $pref) {
            if (str_starts_with($trimmed_address, $pref)) {
                $prefecture = $pref;
                $rest_after_prefecture = trim(mb_substr($trimmed_address, mb_strlen($pref)));
                break;
            }
        }

        $city = null;
        $rest_after_city = $rest_after_prefecture;

        if ($rest_after_prefecture !== '') {
            // 東村山市の特別処理
            if (str_starts_with($rest_after_prefecture, '東村山市')) {
                $city = '東村山市';
                $rest_after_city = trim(mb_substr($rest_after_prefecture, mb_strlen('東村山市')));
            } elseif (preg_match('/^(.+?市.+?区)(.+)/u', $rest_after_prefecture, $matches)) {
                $city = $matches[1];
                $rest_after_city = trim($matches[2]);
            } elseif (preg_match('/^(.+?市)(.+)/u', $rest_after_prefecture, $matches)) {
                $city_name = $matches[1];
                $rest = trim($matches[2]);

                // 市名に「市」が含まれるケースをチェック
                if (str_starts_with($rest, '市')) {
                    $extended_city = $city_name . '市';
                    if (str_starts_with($rest_after_prefecture, $extended_city)) {
                        $city = $extended_city;
                        $rest_after_city = trim(mb_substr($rest_after_prefecture, mb_strlen($extended_city)));
                    } else {
                        $city = $city_name;
                        $rest_after_city = $rest;
                    }
                } else {
                    $city = $city_name;
                    $rest_after_city = $rest;
                }
            } elseif (str_contains($rest_after_prefecture, '郡')) {
                if (
                    preg_match(
                        '/^(.+?郡(?:玉村町|奥多摩町|大町|.+?[町村]))(.+)/u',
                        $rest_after_prefecture,
                        $matches,
                    )
                ) {
                    $city = $matches[1];
                    $rest_after_city = trim($matches[2]);
                } elseif (
                    preg_match(
                        '/^(.+?郡(?:玉村町|奥多摩町|大町|.+?[町村]))$/u',
                        $rest_after_prefecture,
                        $matches,
                    )
                ) {
                    $city = $matches[1];
                    $rest_after_city = '';
                }
            } else {
                $patterns = [
                    '/^(.+?区)(.+)/u',
                    '/^(.+?町)(.+)/u',
                    '/^(.+?村)(.+)/u',
                ];

                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $rest_after_prefecture, $matches)) {
                        $city = $matches[1];
                        $rest_after_city = trim($matches[2]);
                        break;
                    }
                }
            }
        }

        return [
            'prefecture' => $prefecture,
            'city'       => $city,
            'rest'       => $rest_after_city,
        ];
    }
}
