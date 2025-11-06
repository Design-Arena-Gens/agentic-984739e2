<?php
require_once __DIR__ . '/common/header.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? '');
    $tags = sanitize($_POST['tags'] ?? '');

    if (empty($title)) {
        $error = 'Title is required';
    } elseif (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a video file';
    } else {
        $videoUpload = uploadFile($_FILES['video'], ['video/mp4', 'video/webm', 'video/ogg']);

        if (!$videoUpload['success']) {
            $error = $videoUpload['error'];
        } else {
            $videoFile = $videoUpload['filename'];
            $thumbnail = '';

            // Handle thumbnail upload
            if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnailUpload = uploadFile($_FILES['thumbnail'], ['image/jpeg', 'image/png', 'image/jpg']);
                if ($thumbnailUpload['success']) {
                    $thumbnail = $thumbnailUpload['filename'];
                }
            }

            $userId = getCurrentUserId();
            $query = "INSERT INTO videos (user_id, title, description, video_file, thumbnail, category, tags)
                      VALUES ($userId, '$title', '$description', '$videoFile', '$thumbnail', '$category', '$tags')";

            if ($conn->query($query)) {
                $success = 'Video uploaded successfully!';
                $title = $description = $category = $tags = '';
            } else {
                $error = 'Failed to upload video. Please try again.';
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">Upload Video</h1>
        <p class="text-gray-400">Share your content with the world</p>
    </div>

    <div class="bg-dark-card rounded-lg p-6">
        <?php if ($error): ?>
            <div class="bg-red-900/20 border border-red-500 text-red-500 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-900/20 border border-green-500 text-green-500 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <a href="/home.php" class="underline ml-2">Go to Home</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="/upload.php" enctype="multipart/form-data">
            <div class="space-y-6">
                <!-- Video File -->
                <div>
                    <label class="block text-sm font-medium mb-2">Video File *</label>
                    <div class="border-2 border-dashed border-gray-700 rounded-lg p-8 text-center hover:border-primary transition">
                        <i class="fas fa-cloud-upload-alt text-5xl text-gray-600 mb-4"></i>
                        <input type="file" name="video" accept="video/*" required
                            class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-primary file:text-white hover:file:bg-red-700">
                        <p class="text-xs text-gray-500 mt-2">MP4, WebM, or OGG (Max: 100MB)</p>
                    </div>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label class="block text-sm font-medium mb-2">Thumbnail (Optional)</label>
                    <input type="file" name="thumbnail" accept="image/*"
                        class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600">
                </div>

                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium mb-2">Title *</label>
                    <input type="text" name="title" required maxlength="255"
                        value="<?php echo htmlspecialchars($title ?? ''); ?>"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Enter video title">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium mb-2">Description</label>
                    <textarea name="description" rows="4"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Tell viewers about your video"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <select name="category"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary">
                        <option value="">Select Category</option>
                        <option value="Gaming">Gaming</option>
                        <option value="Music">Music</option>
                        <option value="Education">Education</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Sports">Sports</option>
                        <option value="Technology">Technology</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="News">News</option>
                        <option value="Comedy">Comedy</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Tags -->
                <div>
                    <label class="block text-sm font-medium mb-2">Tags</label>
                    <input type="text" name="tags"
                        value="<?php echo htmlspecialchars($tags ?? ''); ?>"
                        class="w-full bg-dark-bg border border-gray-700 rounded-lg px-4 py-3 focus:outline-none focus:border-primary"
                        placeholder="Separate tags with commas">
                </div>

                <!-- Submit -->
                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-primary hover:bg-red-700 py-3 rounded-lg font-semibold transition">
                        <i class="fas fa-upload mr-2"></i>Upload Video
                    </button>
                    <a href="/home.php" class="px-6 py-3 rounded-lg border border-gray-700 hover:bg-dark-hover transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/common/footer.php'; ?>
