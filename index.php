<?php

$url = $_GET['url'] ?? 'home';
$url = trim($url, '/');

switch ($url) {
    case '':
    case 'home':
        require __DIR__ . '/01_front/php/yaju.php';
        break;

    case 'auth':
        require __DIR__ . '/01_front/php/auth.php';
        break;

    case 'post':
        require __DIR__ . '/01_front/php/question_post.php';
        break;

    
    case 'notice':
        require __DIR__ . '/01_front/php/notification.php';
        break;

    case 'ranking':
        require __DIR__ . '/01_front/php/ranking.php';
        break;

    case 'profile':
        require __DIR__ . '/01_front/php/profile.php';
        break;

    /* ===== profile related ===== */
    case 'profile_upload':
        require __DIR__ . '/01_front/php/profile_upload.php';
        break;

    case 'my_questions':
        require __DIR__ . '/01_front/php/my_questions.php';
        break;

    case 'my_answers':
        require __DIR__ . '/01_front/php/my_answers.php';
        break;

    case 'project_add':
        require __DIR__ . '/01_front/php/project_add.php';
        break;

    case 'project_edit':
        require __DIR__ . '/01_front/php/project_edit.php';
        break;

    case 'project_delete':
        require __DIR__ . '/01_front/php/project_delete.php';
        break;

    case 'follow':
        require __DIR__ . '/01_front/php/follow.php';
        break;

    case 'unfollow':
        require __DIR__ . '/01_front/php/unfollow.php';
        break;

    /* ===== if you have direct message page ===== */

    case 'chat':
        require __DIR__ . '/01_front/php/chat_list.php';
        break;

    case 'chat_messages':
        require __DIR__ . '/01_front/php/chat_messages.php';
        break;

    case 'chat_room':
        require __DIR__ . '/01_front/php/chat.php';
        break;

    case 'question_list':
        require __DIR__ . '/01_front/php/question_list.php';
        break;

    case 'question_detail':
        require __DIR__ . '/01_front/php/question_detail.php';
        break;

    case 'answer_post':
        require __DIR__ . '/01_front/php/answer_post.php';
        break;

    case 'best_answer':
        require __DIR__ . '/01_front/php/best_answer.php';
        break;

    case 'helpful':
        require __DIR__ . '/01_front/php/helpful.php';
        break;

    case 'report':
        require __DIR__ . '/01_front/php/report.php';
        break;

    case 'logout':
        require __DIR__ . '/01_front/php/logout.php';
        break;

    case 'user_profile':
        require __DIR__ . '/01_front/php/user_profile.php';
        break;

    case 'question_delete':
        require __DIR__ . '/01_front/php/question_delete.php';
        break;

        /* ===== game  ===== */
    case 'game':
        require __DIR__ . '/01_front/php/arcade.php';
        break;    


    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}
