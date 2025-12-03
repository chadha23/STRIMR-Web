<?php
require_once __DIR__ . '/../../../Controller/AnalyticsController.php';
require_once __DIR__ . '/../../../Controller/PostController.php';

$analyticsController = new AnalyticsController();
$postController = new PostController();

// Get analytics data
$analytics = $analyticsController->getPostAnalytics($_SESSION['user_id'] ?? null);
$recommendations = $analyticsController->getRecommendations($_SESSION['user_id'] ?? null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Analytics - STRIMR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
        }
        .recommendation-card {
            border-left: 4px solid #198754;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Post Analytics</h1>
            <a href="../feed/" class="btn btn-primary">Back to Feed</a>
        </div>

        <!-- Recommendations Section -->
        <?php if (!empty($recommendations)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card recommendation-card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">AI-Powered Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($recommendations as $rec): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($rec['title']); ?></h6>
                                        <p class="card-text"><?php echo htmlspecialchars($rec['description']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="text-muted">Total Posts</h6>
                        <h3><?php echo !empty($analytics['time_analysis']) ? array_sum(array_column($analytics['time_analysis'], 'posts_count')) : 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="text-muted">Average Likes Per Post</h6>
                        <h3><?php echo !empty($analytics['time_analysis']) ? round(array_sum(array_column($analytics['time_analysis'], 'avg_likes')) / count($analytics['time_analysis']), 1) : 0; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <div class="card-body">
                        <h6 class="text-muted">Best Time to Post</h6>
                        <h3>
                            <?php 
                            if (!empty($analytics['time_analysis'])) {
                                $best = $analytics['time_analysis'][0];
                                echo htmlspecialchars($best['day_of_week'] . ' ' . $best['hour_of_day'] . ':00');
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Engagement Trends Chart -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Engagement Trends</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="engagementChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Posts -->
        <?php if (!empty($analytics['top_posts'])): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top Performing Posts</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Post</th>
                                        <th>Likes</th>
                                        <th>Comments</th>
                                        <th>Posted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($analytics['top_posts'] as $post): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(substr($post['content'], 0, 100) . (strlen($post['content']) > 100 ? '...' : '')); ?></td>
                                        <td><?php echo $post['like_count']; ?></td>
                                        <td><?php echo $post['comment_count']; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Engagement Trends Chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('engagementChart').getContext('2d');
            
            // Prepare data for the chart
            const engagementData = <?php 
                echo json_encode([
                    'labels' => array_map(function($hour) { 
                        return $hour . ':00'; 
                    }, array_keys($analytics['engagement_trends']['hourly'] ?? [])),
                    'posts' => array_column($analytics['engagement_trends']['hourly'] ?? [], 'posts'),
                    'likes' => array_column($analytics['engagement_trends']['hourly'] ?? [], 'likes'),
                    'comments' => array_column($analytics['engagement_trends']['hourly'] ?? [], 'comments')
                ]); 
            ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: engagementData.labels,
                    datasets: [
                        {
                            label: 'Posts',
                            data: engagementData.posts,
                            borderColor: 'rgba(54, 162, 235, 1)',
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Likes',
                            data: engagementData.likes,
                            borderColor: 'rgba(255, 99, 132, 1)',
                            backgroundColor: 'rgba(255, 99, 132, 0.2)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Comments',
                            data: engagementData.comments,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Count'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hour of Day'
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Engagement by Hour of Day'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        });
    </script>
</body>
</html>
