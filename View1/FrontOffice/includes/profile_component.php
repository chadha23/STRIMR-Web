<?php
// Profile component - shows user info and logout button
if (!isset($currentUser)) {
    $currentUser = $authController->getCurrentUser();
}
$username = $currentUser['username'] ?? 'User';
$firstLetter = strtoupper(substr($username, 0, 1));
?>
<div class="user-profile-widget" id="user-profile-widget">
    <div class="profile-trigger" id="profile-trigger">
        <div class="profile-avatar"><?php echo htmlspecialchars($firstLetter); ?></div>
        <span class="profile-name"><?php echo htmlspecialchars($username); ?></span>
        <svg class="profile-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
    <div class="profile-dropdown" id="profile-dropdown">
        <div class="profile-info">
            <div class="profile-avatar-large"><?php echo htmlspecialchars($firstLetter); ?></div>
            <div class="profile-details">
                <div class="profile-username"><?php echo htmlspecialchars($username); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></div>
            </div>
        </div>
        <div class="profile-divider"></div>
        <a href="../auth/logout.php" class="profile-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span>Logout</span>
        </a>
    </div>
</div>

<style>
.user-profile-widget {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 1000;
}

.profile-trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: rgba(30, 30, 46, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.profile-trigger:hover {
    background: rgba(40, 40, 56, 0.95);
    border-color: rgba(255, 255, 255, 0.2);
}

.profile-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5865f2 0%, #4752c4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 600;
    font-size: 16px;
    flex-shrink: 0;
}

.profile-name {
    color: #ffffff;
    font-weight: 500;
    font-size: 14px;
    white-space: nowrap;
}

.profile-arrow {
    width: 16px;
    height: 16px;
    color: #a0a0a0;
    transition: transform 0.2s;
    margin-left: auto;
}

.profile-trigger.active .profile-arrow {
    transform: rotate(180deg);
}

.profile-dropdown {
    position: absolute;
    bottom: 60px;
    left: 0;
    width: 280px;
    background: rgba(30, 30, 46, 0.98);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px;
    display: none;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.profile-dropdown.active {
    display: block;
}

.profile-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.profile-avatar-large {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5865f2 0%, #4752c4 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-weight: 600;
    font-size: 20px;
    flex-shrink: 0;
}

.profile-details {
    flex: 1;
    min-width: 0;
}

.profile-username {
    color: #ffffff;
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-email {
    color: #a0a0a0;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.profile-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.1);
    margin: 12px 0;
}

.profile-logout {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    color: #ed4245;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.2s;
    font-size: 14px;
    font-weight: 500;
}

.profile-logout:hover {
    background: rgba(237, 66, 69, 0.1);
}

.profile-logout svg {
    width: 18px;
    height: 18px;
}

@media (max-width: 768px) {
    .user-profile-widget {
        bottom: 10px;
        left: 10px;
    }
    
    .profile-name {
        display: none;
    }
    
    .profile-dropdown {
        width: 240px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileTrigger = document.getElementById('profile-trigger');
    const profileDropdown = document.getElementById('profile-dropdown');
    
    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            profileTrigger.classList.toggle('active');
            profileDropdown.classList.toggle('active');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!profileTrigger.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileTrigger.classList.remove('active');
                profileDropdown.classList.remove('active');
            }
        });
    }
});
</script>
