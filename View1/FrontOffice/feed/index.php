<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../Controller/PostController.php';
require_once __DIR__ . '/../../../Controller/ReactionController.php';
require_once __DIR__ . '/../../../Model/Classes User.php';
require_once __DIR__ . '/../../../Model/Comment Class';
require_once __DIR__ . '/../../../Model/Reacion Class';


$postC = new PostController();
$reactionC = new ReactionController();

$authorId = '1'; // User ID from database (varchar)

if($_SERVER['REQUEST_METHOD']==='POST'){

    if(isset($_POST['content'])){

        if(!empty($_POST['content'])){

            $post = new Post(null, $authorId, $_POST['content'], null, null);

            if($postC->addPost($post)){
                // Redirect to prevent form resubmission
                header('Location: index.php');
                exit();
            }

        }

    }

    if(isset($_POST['toggle_reaction']) && isset($_POST['post_id']) && isset($_POST['user_id'])){
        $post_id = $_POST['post_id'];
        $user_id = $_POST['user_id'];
        $reactionC->toggleReaction($post_id, $user_id, 'heart');
        header('Location: index.php');
        exit();
    }

}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed - Discord Clone</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .heart-btn.liked .heart-icon {
            fill: #e0245e;
            stroke: #e0245e;
        }
        .heart-btn.liked {
            color: #e0245e;
        }
        .heart-btn {
            cursor: pointer;
            transition: all 0.2s;
        }
        .heart-btn:hover {
            color: #e0245e;
        }
    </style>
</head>
<body>
    <!-- ============================================
         TOP NAVIGATION TEMPLATE
         Use the data-page-target attribute to hook your JS
         ============================================ -->
    <nav class="top-nav" aria-label="Primary navigation">
        <div class="nav-container">
            <a class="nav-item" href="../servers/index.html" data-page-target="servers-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M7 8h10M7 12h6m8-1a9 9 0 10-4.5 7.8l3.6 1.2a1 1 0 001.3-1.1l-.6-3A8.9 8.9 0 0021 11z"/>
                    </svg>
                </div>
                <span class="nav-label">Servers</span>
            </a>
            <a class="nav-item" href="../stream/index.html" data-page-target="stream-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M4 6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-3l-4.5 3a1 1 0 01-1.5-.86V17H6a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <span class="nav-label">Stream</span>
            </a>
            <a class="nav-item" href="../marketplace/index.html" data-page-target="marketplace-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9l1.8-3.6A2 2 0 016.6 4h10.8a2 2 0 011.8 1.4L21 9v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9h18M8 13h1.5a1.5 1.5 0 011.5 1.5V20M14 20v-5.5A1.5 1.5 0 0115.5 13H17"/>
                    </svg>
                </div>
                <span class="nav-label">Marketplace</span>
            </a>
            <a class="nav-item active" href="index.php" data-page-target="feed-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M21 5.92a6.55 6.55 0 01-1.89.52 3.3 3.3 0 001.45-1.82 6.59 6.59 0 01-2.07.8 3.28 3.28 0 00-5.6 2.24 3.4 3.4 0 00.08.75A9.31 9.31 0 013 5.16a3.29 3.29 0 001.02 4.38 3.23 3.23 0 01-1.48-.41v.04a3.29 3.29 0 002.63 3.22 3.3 3.3 0 01-1.47.06 3.29 3.29 0 003.07 2.28A6.58 6.58 0 013 17.54 9.29 9.29 0 008.05 19c6.29 0 9.73-5.22 9.73-9.75q0-.23-.01-.45A6.97 6.97 0 0021 5.92z"/>
                    </svg>
                </div>
                <span class="nav-label">Feed</span>
            </a>
            <a class="nav-item" href="../events/index.html" data-page-target="events-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="nav-label">Events</span>
            </a>
        </div>
    </nav>

    <!-- ============================================
         FEED PAGE - STANDALONE TEMPLATE
         Work on this page independently
         ============================================ -->
    <main class="main-wrapper">
        <section id="feed-page" class="page active" data-template-section="feed">
            <div class="feed-page">
                <div class="feed-container">
                    <!-- Compose Box - Create new post -->
                    <form method="POST">

                        <div class="compose-box">
                            <div class="compose-header">
                                <div class="compose-avatar">U</div>

                                <div style="flex: 1;">
                                    <textarea 
                                        class="compose-input"
                                        name="content"
                                        placeholder="What's happening?"
                                        rows="4"
                                        required
                                    ></textarea>
                                </div>
                            </div>

                            <div class="compose-actions">
                                <div class="compose-icons">
                                    <button type="button" class="compose-icon" aria-label="Add image">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12H4V6z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4 16l4.5-4.5a2 2 0 012.83 0L18 19M14.5 10.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="compose-icon" aria-label="Go live">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <rect x="3" y="5" width="14" height="14" rx="2" ry="2" stroke-width="1.8"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M17 9l4-2v10l-4-2z"/>
                                        </svg>
                                    </button>

                                    <button type="button" class="compose-icon" aria-label="Add emoji">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M8 15s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Like AddPersonne submit button -->
                                <button type="submit" class="compose-btn">Tweet</button>
                            </div>
                        </div>
                    </form>

                    <?php 
                    $list = $postC->getAllPosts();
                    ?>

                    <div class="posts-container" id="posts-container">
                        <?php foreach($list as $post){ 
                            $reactionCount = $reactionC->getReactionCount($post['id'], 'heart');
                            $hasReacted = $reactionC->hasUserReacted($post['id'], $authorId, 'heart');
                        ?>
                            <article class="post" data-template-item="post">
                                <header class="post-header">
                                    <div class="post-user-info">
                                        <p class="post-username"><?php echo $post['username']; ?></p>
                                        <p class="post-handle">@<?php echo $post['username']; ?> · <?php echo $post['created_at']; ?></p>
                                    </div>
                                    <button class="post-more" type="button" aria-label="More options">⋯</button>
                                </header>
                                <div class="post-content">
                                    <?php echo $post['content']; ?>
                                </div>
                                <div class="post-actions">
                                    <button class="post-action reply" type="button">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M10 9V5a7 7 0 017 7h3l-4 4-4-4h3a4 4 0 00-4-4z"/>
                                        </svg>
                                        <span>0</span>
                                    </button>
                                    <button class="post-action retweet" type="button">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M17 1l4 4-4 4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M3 11v-2a4 4 0 014-4h14"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M7 23l-4-4 4-4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M21 13v2a4 4 0 01-4 4H3"/>
                                        </svg>
                                        <span>0</span>
                                    </button>
                                    <form method="POST" action="index.php" style="display: inline;">
                                        <input type="hidden" name="toggle_reaction" value="1">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $authorId; ?>">
                                        <button class="post-action like heart-btn <?php echo $hasReacted ? 'liked' : ''; ?>" 
                                                type="submit">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="heart-icon">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 21l-1.45-1.32C5.4 15.36 2 12.28 2 8.5A4.5 4.5 0 016.5 4 4.11 4.11 0 0112 6.09 4.11 4.11 0 0117.5 4 4.5 4.5 0 0122 8.5c0 3.78-3.4 6.86-8.55 11.18z"/>
                                            </svg>
                                            <span class="reaction-count"><?php echo $reactionCount; ?></span>
                                        </button>
                                    </form>
                                    <button class="post-action share" type="button">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M4 12v7a1 1 0 001 1h14a1 1 0 001-1v-7"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M16 6l-4-4-4 4"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 2v13"/>
                                        </svg>
                                    </button>
                                    <div style="margin-left: auto; display: flex; gap: 8px; align-items: center;">
                                        <form method="POST" action="updatePost.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                            <input type="submit" name="update" value="Update" class="action-btn btn-edit">
                                        </form>
                                        <a href="delete.php?id=<?php echo $post['id']; ?>" 
                                           class="action-btn btn-delete"
                                           onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>

