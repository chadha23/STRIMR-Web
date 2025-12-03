<?php
require_once __DIR__ . '/../../../Controller/PostController.php';
require_once __DIR__ . '/../../../Model/Classes User.php';
require_once __DIR__ . '/../../../Model/Comment Class';
require_once __DIR__ . '/../../../Model/Reacion Class';


$postC = new PostController();
$authorId = '1'; // User ID from database (varchar)

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['content'])){
        if(!empty($_POST['content'])){
            $post = new Post(null, $authorId, $_POST['content'], null, null);
            
            if($postC->addPost($post)){
                header('Location: index.php');
                exit();
            } else {
                $error = 'Error creating post. Please try again.';
            }
        } else {
            $error = 'Content cannot be empty.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post - Admin Panel</title>
    <link rel="stylesheet" href="../admin-styles.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #2f3136;
            border-radius: 8px;
        }
        .form-container label {
            display: block;
            margin-bottom: 8px;
            color: #b9bbbe;
            font-weight: 500;
        }
        .form-container textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #40444b;
            border-radius: 4px;
            font-size: 14px;
            background: #36393f;
            color: #ffffff;
            font-family: inherit;
            min-height: 150px;
            resize: vertical;
            box-sizing: border-box;
        }
        .form-container textarea:focus {
            outline: none;
            border-color: #5865f2;
        }
        .form-container button {
            padding: 12px 24px;
            background: #5865f2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-top: 15px;
        }
        .form-container button:hover {
            background: #4752c4;
        }
        .form-container .cancel-btn {
            background: #4f545c;
            margin-left: 10px;
        }
        .form-container .cancel-btn:hover {
            background: #5d6269;
        }
        .error {
            color: #f04747;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(240, 71, 71, 0.1);
            border-radius: 4px;
        }
        .success {
            color: #43b581;
            margin-bottom: 15px;
            padding: 10px;
            background: rgba(67, 181, 129, 0.1);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Admin Panel</h2>
                <div class="admin-avatar">A</div>
            </div>

            <nav class="sidebar-nav">
                <a href="../dashboard/index.html" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="index.php" class="nav-link active">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Posts</span>
                </a>
            </nav>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title">Add New Post</h1>
                    <p class="page-subtitle">Create a new post</p>
                </div>
            </header>

            <div class="form-container">
                <?php if ($error) echo '<div class="error">' . htmlspecialchars($error) . '</div>'; ?>
                <?php if ($success) echo '<div class="success">' . htmlspecialchars($success) . '</div>'; ?>

                <form method="POST" action="">
                    <label for="content">Post Content:</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        placeholder="Enter post content here..."
                        required
                    ></textarea>

                    <div>
                        <button type="submit">Create Post</button>
                        <a href="index.php" class="cancel-btn" style="text-decoration: none; display: inline-block; padding: 12px 24px; background: #4f545c; color: white; border-radius: 4px;">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

