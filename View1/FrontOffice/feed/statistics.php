<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Authentication check
require_once __DIR__ . '/../includes/auth_check.php';

require_once __DIR__ . '/../../../Controller/StatisticsController.php';

$statsController = new StatisticsController();
$stats = $statsController->getAllStatistics();
$aiInsights = $stats ? $statsController->getAIInsights($stats) : "No data available.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Feed Analytics</title>
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-page {
            background: radial-gradient(circle at 0% 30%, rgba(88, 101, 242, 0.2) 0%, transparent 52%),
                        radial-gradient(circle at 100% 10%, rgba(67, 181, 129, 0.18) 0%, transparent 55%),
                        rgba(9, 11, 24, 0.85);
            min-height: calc(100vh - 112px);
            border-radius: 24px;
            box-shadow: 0 24px 50px rgba(6, 9, 26, 0.55);
            padding: 40px;
        }
        
        .stats-header {
            margin-bottom: 40px;
        }
        
        .stats-header h1 {
            color: #ffffff;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stats-header p {
            color: #a0a0a0;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(10px);
        }
        
        .stat-card h3 {
            color: #a0a0a0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        
        .stat-card .value {
            color: #ffffff;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .stat-card .change {
            color: #43b581;
            font-size: 14px;
        }
        
        .ai-insights {
            background: rgba(88, 101, 242, 0.1);
            border: 1px solid rgba(88, 101, 242, 0.3);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 40px;
        }
        
        .ai-insights h2 {
            color: #ffffff;
            font-size: 20px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ai-insights .insight-text {
            color: #d0d0d0;
            font-size: 16px;
            line-height: 1.6;
            white-space: pre-line;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .chart-card {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 24px;
            backdrop-filter: blur(10px);
        }
        
        .chart-card h3 {
            color: #ffffff;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .back-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #5865f2;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: background 0.2s;
        }
        
        .back-btn:hover {
            background: #4752c4;
        }
    </style>
</head>
<body>
    <nav class="top-nav" aria-label="Primary navigation">
        <div class="nav-container">
            <a class="nav-item" href="../servers/index.php" data-page-target="servers-page">
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
            <a class="nav-item" href="index.php" data-page-target="feed-page">
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

    <main class="main-wrapper">
        <div class="stats-page">
            <a href="index.php" class="back-btn">← Back to Feed</a>
            
            <div class="stats-header">
                <h1>📊 Feed Statistics</h1>
                <p>AI-powered insights about your posts and engagement</p>
            </div>

            <?php if ($stats): ?>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Posts</h3>
                        <div class="value"><?php echo $stats['total_posts']; ?></div>
                        <div class="change">All time</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Total Likes</h3>
                        <div class="value"><?php echo $stats['total_likes']; ?></div>
                        <div class="change">❤️ Hearts received</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Avg Likes/Post</h3>
                        <div class="value"><?php echo $stats['average_likes_per_post']; ?></div>
                        <div class="change">Per post average</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3>Engagement Rate</h3>
                        <div class="value"><?php echo $stats['engagement_rate']; ?>%</div>
                        <div class="change">Overall engagement</div>
                    </div>
                </div>

                <div class="ai-insights">
                    <h2>
                        <span>🤖</span>
                        AI Insights
                    </h2>
                    <div class="insight-text"><?php echo htmlspecialchars($aiInsights); ?></div>
                </div>

                <div class="charts-container">
                    <div class="chart-card">
                        <h3>Posts by Date</h3>
                        <canvas id="postsByDateChart"></canvas>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Likes by Date</h3>
                        <canvas id="likesByDateChart"></canvas>
                    </div>
                    
                    <div class="chart-card">
                        <h3>Posts by Hour</h3>
                        <canvas id="postsByHourChart"></canvas>
                    </div>
                </div>

                <?php if ($stats['most_liked_post']): ?>
                <div class="stat-card">
                    <h3>Most Liked Post</h3>
                    <div style="color: #ffffff; margin-top: 12px;">
                        <p style="margin-bottom: 8px;"><strong><?php echo $stats['most_liked_post']['likes']; ?> likes</strong></p>
                        <p style="color: #a0a0a0; font-size: 14px; margin-bottom: 4px;"><?php echo htmlspecialchars($stats['most_liked_post']['content']); ?></p>
                        <p style="color: #666; font-size: 12px;">By <?php echo htmlspecialchars($stats['most_liked_post']['author']); ?> on <?php echo $stats['most_liked_post']['date']; ?></p>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="stat-card">
                    <h3>No Data Available</h3>
                    <p style="color: #a0a0a0;">Start posting to see your statistics!</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php if ($stats): ?>
    <script>
        // Posts by Date Chart
        const postsByDateCtx = document.getElementById('postsByDateChart').getContext('2d');
        new Chart(postsByDateCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($stats['posts_by_date'])); ?>,
                datasets: [{
                    label: 'Posts',
                    data: <?php echo json_encode(array_values($stats['posts_by_date'])); ?>,
                    borderColor: '#5865f2',
                    backgroundColor: 'rgba(88, 101, 242, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: '#ffffff' }
                    }
                },
                scales: {
                    y: {
                        ticks: { color: '#a0a0a0' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#a0a0a0' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });

        // Likes by Date Chart
        const likesByDateCtx = document.getElementById('likesByDateChart').getContext('2d');
        new Chart(likesByDateCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($stats['likes_by_date'])); ?>,
                datasets: [{
                    label: 'Likes',
                    data: <?php echo json_encode(array_values($stats['likes_by_date'])); ?>,
                    backgroundColor: '#43b581',
                    borderColor: '#43b581'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: '#ffffff' }
                    }
                },
                scales: {
                    y: {
                        ticks: { color: '#a0a0a0' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#a0a0a0' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });

        // Posts by Hour Chart
        const postsByHourCtx = document.getElementById('postsByHourChart').getContext('2d');
        new Chart(postsByHourCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($h) { return $h . ':00'; }, array_keys($stats['posts_by_hour']))); ?>,
                datasets: [{
                    label: 'Posts',
                    data: <?php echo json_encode(array_values($stats['posts_by_hour'])); ?>,
                    backgroundColor: '#e0245e',
                    borderColor: '#e0245e'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: '#ffffff' }
                    }
                },
                scales: {
                    y: {
                        ticks: { color: '#a0a0a0' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: '#a0a0a0' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>

