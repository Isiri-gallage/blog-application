<?php
require_once 'config/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Enhanced Editor Styles */
        .editor-container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .editor-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .editor-panel {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .panel-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 15px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
        }
        
        .toolbar-btn {
            padding: 8px 12px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            color: #495057;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .toolbar-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }
        
        .toolbar-btn:active {
            background: #dee2e6;
        }
        
        .toolbar-separator {
            width: 1px;
            background: #dee2e6;
            margin: 0 4px;
        }
        
        .form-group-enhanced {
            padding: 20px;
        }
        
        .form-group-enhanced label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group-enhanced input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.2s ease;
        }
        
        .form-group-enhanced input[type="text"]:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .form-group-enhanced textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 15px;
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            line-height: 1.6;
            resize: vertical;
            min-height: 500px;
            transition: border-color 0.2s ease;
        }
        
        .form-group-enhanced textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        /* Featured Image Upload Styles */
        .image-upload-container {
            border: 2px dashed #dee2e6;
            border-radius: 4px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .image-upload-container:hover {
            border-color: #3498db;
            background: #f8f9fa;
        }
        
        .image-upload-container.has-image {
            border-style: solid;
            padding: 0;
        }
        
        .upload-placeholder {
            color: #6c757d;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #adb5bd;
            margin-bottom: 10px;
        }
        
        .image-preview {
            position: relative;
            width: 100%;
        }
        
        .image-preview img {
            width: 100%;
            height: auto;
            border-radius: 4px;
            display: block;
        }
        
        .remove-image-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s ease;
        }
        
        .remove-image-btn:hover {
            background: #c82333;
        }
        
        .preview-content {
            padding: 20px;
            min-height: 500px;
            line-height: 1.8;
            color: #2c3e50;
        }
        
        .preview-content h1,
        .preview-content h2,
        .preview-content h3 {
            margin-top: 1.5em;
            margin-bottom: 0.5em;
        }
        
        .preview-content p {
            margin-bottom: 1em;
        }
        
        .preview-content code {
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Consolas', 'Monaco', monospace;
        }
        
        .preview-content pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
        
        .form-actions-enhanced {
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }
        
        .page-title-enhanced {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 30px;
        }
        
        /* Logo styling */
        .logo img {
            height: 70px;
            display: block;
            transition: opacity 0.2s ease;
        }
        
        .logo:hover img {
            opacity: 0.9;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .editor-layout {
                grid-template-columns: 1fr;
            }
            
            .preview-panel {
                order: 2;
            }
        }
        
        /* Empty preview state */
        .preview-empty {
            color: #adb5bd;
            font-style: italic;
            text-align: center;
            padding: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/images/logo.svg" alt="Momentum" />
            </a>
            <div class="nav-links">
                <span class="user-info">Hello, <?php echo $currentUser['username']; ?></span>
                <a href="profile.php" class="btn btn-secondary">My Profile</a>
                <a href="index.php" class="btn btn-secondary">Home</a>
                <a href="api/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container main-content editor-container">
        <h1 class="page-title-enhanced">Create New Blog Post</h1>
        <p class="page-subtitle">Share your story with the world using our powerful markdown editor</p>
        
        <form id="createBlogForm" enctype="multipart/form-data">
            <div class="editor-panel" style="margin-bottom: 20px;">
                <div class="panel-header">Blog Title</div>
                <div class="form-group-enhanced">
                    <input type="text" id="title" name="title" required placeholder="Enter your blog title...">
                </div>
            </div>
            
            <!-- Featured Image Upload -->
            <div class="editor-panel" style="margin-bottom: 20px;">
                <div class="panel-header">Featured Image (Optional)</div>
                <div class="form-group-enhanced">
                    <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display: none;">
                    <div class="image-upload-container" id="imageUploadContainer" onclick="document.getElementById('featured_image').click()">
                        <div class="upload-placeholder" id="uploadPlaceholder">
                            <div class="upload-icon">📷</div>
                            <p><strong>Click to upload</strong> or drag and drop</p>
                            <p style="font-size: 14px; color: #adb5bd;">PNG, JPG, GIF or WEBP (Max 5MB)</p>
                        </div>
                        <div class="image-preview" id="imagePreview" style="display: none;">
                            <img id="previewImg" src="" alt="Preview">
                            <button type="button" class="remove-image-btn" onclick="removeImage(event)">Remove</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="editor-layout">
                <!-- Editor Panel -->
                <div class="editor-panel">
                    <div class="panel-header">Write</div>
                    
                    <div class="editor-toolbar">
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('bold')" title="Bold">
                            <strong>B</strong>
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('italic')" title="Italic">
                            <em>I</em>
                        </button>
                        
                        <div class="toolbar-separator"></div>
                        
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('h1')" title="Heading 1">
                            H1
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('h2')" title="Heading 2">
                            H2
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('h3')" title="Heading 3">
                            H3
                        </button>
                        
                        <div class="toolbar-separator"></div>
                        
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('link')" title="Insert Link">
                            🔗 Link
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('code')" title="Code Block">
                            💻 Code
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('quote')" title="Quote">
                            💬 Quote
                        </button>
                        
                        <div class="toolbar-separator"></div>
                        
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('ul')" title="Bullet List">
                            • List
                        </button>
                        <button type="button" class="toolbar-btn" onclick="insertMarkdown('ol')" title="Numbered List">
                            1. List
                        </button>
                    </div>
                    
                    <div class="form-group-enhanced">
                        <textarea id="content" name="content" required placeholder="Write your blog content here..."></textarea>
                    </div>
                </div>
                
                <!-- Preview Panel -->
                <div class="editor-panel preview-panel">
                    <div class="panel-header">Preview</div>
                    <div id="preview" class="preview-content">
                        <div class="preview-empty">Start writing to see the preview...</div>
                    </div>
                </div>
            </div>
            
            <div class="editor-panel" style="margin-top: 20px;">
                <div class="form-actions-enhanced">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Publish Blog</button>
                </div>
            </div>
        </form>
    </div>
    
    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <script>
        const textarea = document.getElementById('content');
        const preview = document.getElementById('preview');
        const fileInput = document.getElementById('featured_image');
        const imageContainer = document.getElementById('imageUploadContainer');
        const placeholder = document.getElementById('uploadPlaceholder');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        // Store the selected image URL for preview
        let selectedImageDataUrl = null;
        
        // Handle image selection
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size must be less than 5MB');
                    fileInput.value = '';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    selectedImageDataUrl = e.target.result;
                    previewImg.src = e.target.result;
                    placeholder.style.display = 'none';
                    imagePreview.style.display = 'block';
                    imageContainer.classList.add('has-image');
                    
                    // Update preview
                    updatePreview();
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Remove image
        function removeImage(e) {
            e.stopPropagation();
            fileInput.value = '';
            previewImg.src = '';
            selectedImageDataUrl = null;
            placeholder.style.display = 'block';
            imagePreview.style.display = 'none';
            imageContainer.classList.remove('has-image');
            
            // Update preview
            updatePreview();
        }
        
        // Insert markdown formatting
        function insertMarkdown(type) {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const selectedText = textarea.value.substring(start, end);
            const beforeText = textarea.value.substring(0, start);
            const afterText = textarea.value.substring(end);
            let newText = '';
            let cursorPos = start;
            
            switch(type) {
                case 'bold':
                    newText = `**${selectedText || 'bold text'}**`;
                    cursorPos = start + (selectedText ? newText.length : 2);
                    break;
                case 'italic':
                    newText = `*${selectedText || 'italic text'}*`;
                    cursorPos = start + (selectedText ? newText.length : 1);
                    break;
                case 'h1':
                    newText = `# ${selectedText || 'Heading 1'}`;
                    cursorPos = start + newText.length;
                    break;
                case 'h2':
                    newText = `## ${selectedText || 'Heading 2'}`;
                    cursorPos = start + newText.length;
                    break;
                case 'h3':
                    newText = `### ${selectedText || 'Heading 3'}`;
                    cursorPos = start + newText.length;
                    break;
                case 'link':
                    newText = `[${selectedText || 'link text'}](url)`;
                    cursorPos = start + newText.length - 1;
                    break;
                case 'code':
                    newText = '```\n' + (selectedText || 'code here') + '\n```';
                    cursorPos = start + (selectedText ? newText.length : 4);
                    break;
                case 'quote':
                    newText = `> ${selectedText || 'quote text'}`;
                    cursorPos = start + newText.length;
                    break;
                case 'ul':
                    newText = `- ${selectedText || 'list item'}`;
                    cursorPos = start + newText.length;
                    break;
                case 'ol':
                    newText = `1. ${selectedText || 'list item'}`;
                    cursorPos = start + newText.length;
                    break;
            }
            
            textarea.value = beforeText + newText + afterText;
            textarea.focus();
            textarea.setSelectionRange(cursorPos, cursorPos);
            
            // Trigger preview update
            updatePreview();
        }
        
        // Update preview with content and image
        function updatePreview() {
            const markdown = textarea.value;
            
            if (markdown.trim() === '' && !selectedImageDataUrl) {
                preview.innerHTML = '<div class="preview-empty">Start writing to see the preview...</div>';
                return;
            }
            
            let previewHtml = '';
            
            // Add featured image to preview if selected
            if (selectedImageDataUrl) {
                previewHtml += `
                    <div style="margin-bottom: 2rem; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <img src="${selectedImageDataUrl}" alt="Featured Image" style="width: 100%; height: auto; display: block; max-height: 400px; object-fit: cover;">
                    </div>
                `;
            }
            
            // Add markdown content
            if (markdown.trim()) {
                previewHtml += markdownToHtml(markdown);
            }
            
            preview.innerHTML = previewHtml || '<div class="preview-empty">Start writing to see the preview...</div>';
        }
        
        // Live preview
        textarea.addEventListener('input', updatePreview);
        
        // Form submission
        document.getElementById('createBlogForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/create-blog.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'view-blog.php?id=' + data.blog_id;
                } else {
                    alert(data.message || 'Failed to create blog');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });
        
        // Keyboard shortcuts
        textarea.addEventListener('keydown', (e) => {
            // Ctrl+B or Cmd+B for bold
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                insertMarkdown('bold');
            }
            // Ctrl+I or Cmd+I for italic
            if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
                e.preventDefault();
                insertMarkdown('italic');
            }
            // Ctrl+K or Cmd+K for link
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                insertMarkdown('link');
            }
        });
    </script>
</body>
</html>