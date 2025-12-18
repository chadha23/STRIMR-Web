<?php
// Authentication check
require_once __DIR__ . '/../includes/auth_check.php';

require_once __DIR__ . '/../../../Controller/PostController.php';
require_once __DIR__ . '/../../../Model/Classes User.php';
require_once __DIR__ . '/../../../Model/Comment Class';
require_once __DIR__ . '/../../../Model/Reacion Class';


$postController = new PostController();

$error = '';
$success = false;

$post = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
} elseif (isset($_POST['id'])) {
    $id = $_POST['id'];
} else {
    header('Location: index.php');
    exit();
}

$post = $postController->showPost($id);

if (!$post) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['content'])) {
    if (!empty($_POST['content'])) {
        $p = new Post(
            $id,
            $post['author_id'],
            $_POST['content'],
            $post['created_at'],
            null
        );

        if($postController->updatePost($p, $id)){
            $success = true;
            // Redirect after 1 second to show success message
            header('refresh:1;url=index.php');
        } else {
            $error = 'Error updating post. Please try again.';
        }
    } else {
        $error = 'Please fill in the content field';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - STRIMR</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .update-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 40px;
            background: radial-gradient(circle at 0% 0%, rgba(88, 101, 242, 0.35) 0%, transparent 45%),
                        radial-gradient(circle at 100% 0%, rgba(250, 166, 26, 0.25) 0%, transparent 40%),
                        radial-gradient(circle at 50% 100%, rgba(224, 30, 90, 0.25) 0%, transparent 55%),
                        #0b0d16;
        }

        .update-card {
            width: 100%;
            max-width: 700px;
            background: rgba(16, 19, 32, 0.85);
            backdrop-filter: blur(20px) saturate(140%);
            border: 1px solid rgba(120, 125, 255, 0.2);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(9, 11, 22, 0.6),
                        0 0 0 1px rgba(120, 125, 255, 0.1);
            transition: all 0.3s ease;
        }

        .update-card:hover {
            border-color: rgba(120, 125, 255, 0.3);
            box-shadow: 0 25px 70px rgba(9, 11, 22, 0.7),
                        0 0 0 1px rgba(120, 125, 255, 0.15);
        }

        .update-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .update-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #ffffff 0%, #a0a0ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .update-header p {
            color: #a0a0a0;
            font-size: 14px;
        }

        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .message.success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .message-icon {
            font-size: 20px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            color: #b9bbbe;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .form-textarea {
            width: 100%;
            min-height: 200px;
            padding: 16px 20px;
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            font-family: inherit;
            line-height: 1.6;
            resize: vertical;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .form-textarea:focus {
            outline: none;
            border-color: rgba(88, 101, 242, 0.6);
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 3px rgba(88, 101, 242, 0.1),
                        inset 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .form-textarea::placeholder {
            color: #6b7280;
        }

        .char-count {
            text-align: right;
            color: #6b7280;
            font-size: 12px;
            margin-top: 8px;
        }

        .char-count.warning {
            color: #f59e0b;
        }

        .char-count.error {
            color: #ef4444;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #5865f2 0%, #4752c4 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(88, 101, 242, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #4752c4 0%, #3c45a5 100%);
            box-shadow: 0 6px 16px rgba(88, 101, 242, 0.4);
            transform: translateY(-1px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .btn-icon {
            width: 18px;
            height: 18px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 14px;
            margin-top: 20px;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: #ffffff;
        }

        .back-link svg {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .update-container {
                padding: 80px 16px 30px;
            }

            .update-card {
                padding: 30px 24px;
                border-radius: 16px;
            }

            .update-header h1 {
                font-size: 24px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="update-container">
        <div class="update-card">
            <div class="update-header">
                <h1>✏️ Edit Post</h1>
                <p>Update your post content below</p>
            </div>

            <?php if ($error): ?>
                <div class="message error">
                    <span class="message-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="message success">
                    <span class="message-icon">✓</span>
                    <span>Post updated successfully! Redirecting...</span>
                </div>
            <?php endif; ?>

            <form action="" method="POST" onsubmit="return validateForm()" id="updateForm">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">

                <div class="form-group">
                    <label for="content" class="form-label">Post Content</label>
                    <textarea 
                        id="content" 
                        name="content" 
                        class="form-textarea" 
                        placeholder="What's on your mind?"
                        maxlength="1000"
                        required
                    ><?php echo htmlspecialchars($post['content']); ?></textarea>
                    <div class="char-count" id="charCount">0 / 1000 characters</div>
                </div>

                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Update Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const contentTextarea = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        const maxLength = 1000;

        function updateCharCount() {
            const length = contentTextarea.value.length;
            const remaining = maxLength - length;
            
            charCount.textContent = `${length} / ${maxLength} characters`;
            
            // Update color based on remaining characters
            charCount.classList.remove('warning', 'error');
            if (remaining < 50) {
                charCount.classList.add('error');
            } else if (remaining < 100) {
                charCount.classList.add('warning');
            }
        }

        // Update character count on input
        contentTextarea.addEventListener('input', updateCharCount);
        
        // Initial count
        updateCharCount();

        function validateForm() {
            const content = contentTextarea.value.trim();
            if (content === '') {
                alert('Please fill in the content field');
                contentTextarea.focus();
                return false;
            }
            if (content.length > maxLength) {
                alert(`Content cannot exceed ${maxLength} characters`);
                contentTextarea.focus();
                return false;
            }
            return true;
        }

        // Auto-resize textarea
        contentTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.max(200, this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>
