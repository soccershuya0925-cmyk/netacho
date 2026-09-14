<?php

// 入力エラーの日本語メッセージ（使っているルールだけ）
return [
    'required' => ':attributeは必ず入力してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
        'file' => ':attributeは:max KB以内のファイルにしてください。',
    ],
    'image' => ':attributeには画像ファイルを選んでください。',
    'date' => ':attributeには正しい日付を入れてください。',
    'in' => ':attributeに選べない値が入っています。',
    'exists' => '選んだ:attributeが見つかりません。',
    'unique' => 'その:attributeはすでに登録されています。',

    // :attribute に入る名前（画面の言葉に合わせる）
    'attributes' => [
        'name' => '商品名',
        'description' => '特徴・売り',
        'image' => '商品写真',
        'title' => '見出し',
        'body' => '本文',
        'product_id' => '商品',
        'platform' => '出す先',
        'scheduled_for' => '出す予定の日',
        'posted_at' => '投稿した日時',
        'tags' => 'タグ',
    ],
];
