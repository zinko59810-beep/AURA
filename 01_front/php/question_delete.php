<?php
require_once(__DIR__ . '/../../02_backend/db/db_.php');
require_once __DIR__ . '/../../lang/language.php';


if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id = $_POST['id'] ?? 0;
    $dbh = db_connect();

    $stmt = $dbh->prepare("
        SELECT user_id
        FROM questions
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if (!$post || $post['user_id'] != $_SESSION['user_id']) {
        exit("You cannot delete this post");
    }

    $dbh->beginTransaction();

    try {
        $answer_stmt = $dbh->prepare("
            SELECT id
            FROM answers
            WHERE question_id = ?
        ");
        $answer_stmt->execute([$id]);
        $answer_ids = $answer_stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($answer_ids)) {
            $placeholders = implode(',', array_fill(0, count($answer_ids), '?'));

            $stmt = $dbh->prepare("
                DELETE FROM helpfuls
                WHERE answer_id IN ($placeholders)
            ");
            $stmt->execute($answer_ids);

            $stmt = $dbh->prepare("
                DELETE FROM reports
                WHERE answer_id IN ($placeholders)
            ");
            $stmt->execute($answer_ids);
        }

        $stmt = $dbh->prepare("
            DELETE FROM answers
            WHERE question_id = ?
        ");
        $stmt->execute([$id]);

        $stmt = $dbh->prepare("
            DELETE FROM reports
            WHERE question_id = ?
        ");
        $stmt->execute([$id]);

        $stmt = $dbh->prepare("
            DELETE FROM questions
            WHERE id = ?
            AND user_id = ?
        ");
        $stmt->execute([
            $id,
            $_SESSION['user_id']
        ]);

        $dbh->commit();

        header("Location: /AURA/question_list");
        exit;

    } catch (Exception $e) {
        $dbh->rollBack();
        echo "削除に失敗しました";
        exit;
    }
}