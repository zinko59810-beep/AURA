<?php

function aura_save_uploaded_image(
    array $file,
    string $absoluteDir,
    string $dbPrefix,
    int $maxBytes = 5242880
): array {

    $error =
        $file['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_NO_FILE) {
        return [
            'path' => null,
            'error' => '画像を選択してください。'
        ];
    }

    if ($error !== UPLOAD_ERR_OK) {
        return [
            'path' => null,
            'error' => '画像のアップロードに失敗しました。'
        ];
    }

    $size = (int)($file['size'] ?? 0);

    if ($size <= 0 || $size > $maxBytes) {
        return [
            'path' => null,
            'error' => '画像は5MB以下にしてください。'
        ];
    }

    $tmp =
        $file['tmp_name'] ?? '';

    if (
        $tmp === '' ||
        !is_uploaded_file($tmp)
    ) {
        return [
            'path' => null,
            'error' => '不正なアップロードです。'
        ];
    }

    $finfo =
        new finfo(FILEINFO_MIME_TYPE);

    $mime =
        $finfo->file($tmp);

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        return [
            'path' => null,
            'error' =>
                'JPG、PNG、WEBPのみアップロードできます。'
        ];
    }

    if (
        !is_dir($absoluteDir) &&
        !mkdir($absoluteDir, 0755, true)
    ) {
        return [
            'path' => null,
            'error' =>
                'アップロードフォルダを作成できません。'
        ];
    }

    $fileName =
        bin2hex(random_bytes(16)) .
        '.' .
        $allowed[$mime];

    $absolutePath =
        rtrim(
            $absoluteDir,
            DIRECTORY_SEPARATOR
        ) .
        DIRECTORY_SEPARATOR .
        $fileName;

    if (
        !move_uploaded_file(
            $tmp,
            $absolutePath
        )
    ) {
        return [
            'path' => null,
            'error' =>
                '画像を保存できませんでした。'
        ];
    }

    return [
        'path' =>
            rtrim($dbPrefix, '/') .
            '/' .
            $fileName,

        'error' => null
    ];
}