# Blog Application

A full-featured blog application built with PHP, MySQL, HTML, CSS, and JavaScript.

## 📋 Features

### User Authentication & Authorization
- User registration with validation
- Secure login/logout system
- Password hashing for security
- Session-based authentication
  
### Blog Management
- Create new blog posts with Markdown support
- Read all blog posts on home page
- Update your own blog posts
- Delete your own blog posts
- View individual blog posts with full content
  
### User Profiles ✨ NEW
- Each user has their own profile page
- Display user statistics (total blogs, total likes received)
- Editable bio section
- View all blogs by a specific user
- Profile avatar with user initial
- Click on any author name to view their profile

### Comments System ✨ NEW
- Users can comment on any blog post
- Real-time comment posting without page reload
- Delete your own comments
- Comment count displayed on blog cards and posts
- View all comments with author and timestamp
- Login required to post comments

### Likes System ✨ NEW
- Like/unlike blog posts with a single click
- Visual feedback (heart button turns red when liked)
- Like count displayed on blogs
- Only logged-in users can like posts
- Persistent likes stored in database
- See total likes received on user profiles
  
### Authorization
- Only authenticated users can create blogs
- Users can only edit/delete their own blogs
- Users can only delete their own comments
- Protection against unauthorized access
  
### Responsive Design
- Mobile-friendly interface
- Clean and modern UI
- Easy navigation
- Smooth animations and transitions

## 🛠️ Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Server**: Apache (XAMPP)

## 📦 Installation

### Prerequisites

- XAMPP (or any PHP 7.4+ and MySQL environment)
- Web browser
- Git (optional)

### Step 1: Setup XAMPP

1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start Apache and MySQL from XAMPP Control Panel

### Step 2: Clone or Download Project

**Option A: Using Git**
```bash
cd C:\xampp\htdocs
git clone <your-repository-url> blog-app
```

**Option B: Manual Download**
1. Download the project files
2. Extract to `C:\xampp\htdocs\blog-app`

### Step 3: Setup Environment Variables

1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit `.env` file with your configuration:
   ```
   DB_HOST=localhost
   DB_NAME=blog_db
   DB_USER=root
   DB_PASS=
   
   APP_NAME=My Blog Application
   APP_URL=http://localhost/blog-app
   SESSION_NAME=blog_session
   SESSION_LIFETIME=3600
   ```

### Step 4: Create Database

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create a new database
3. Database name: `blog_db`
4. Collation: `utf8mb4_general_ci`
5. Click "Create"

### Step 5: Initialize Database Tables

**Option A: Using install.php (Initial Setup)**
1. Navigate to `http://localhost/blog-app/install.php`
2. Click "Install Database" button
3. Tables will be created automatically

**Option B: Using update-database.php (For Updates)**
1. Navigate to `http://localhost/blog-app/update-database.php`
2. Click "Update Database" button
3. New tables (comment, blog_like) will be created

**Option C: Manual SQL**

Run this SQL in phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS user (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_post (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS comment (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    blog_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blog_post(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_like (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    blog_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (blog_id, user_id),
    FOREIGN KEY (blog_id) REFERENCES blog_post(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Step 6: Access the Application

Open your browser and navigate to:
```
http://localhost/blog-app
```

## 📁 Project Structure

```
blog-app/
│
├── config/
│   ├── config.php          # Configuration and environment loader
│   └── database.php        # Database connection
│
├── includes/
│   ├── auth.php            # Authentication functions
│   └── functions.php       # Helper functions
│
├── api/
│   ├── register.php        # User registration endpoint
│   ├── login.php           # User login endpoint
│   ├── logout.php          # User logout endpoint
│   ├── create-blog.php     # Create blog endpoint
│   ├── update-blog.php     # Update blog endpoint
│   ├── delete-blog.php     # Delete blog endpoint
│   ├── add-comment.php     # Add comment endpoint ✨
│   ├── delete-comment.php  # Delete comment endpoint ✨
│   └── toggle-like.php     # Toggle like endpoint ✨
│
├── assets/
│   ├── css/
│   │   └── style.css       # Main stylesheet
│   └── js/
│       └── main.js         # JavaScript functions
│
├── index.php               # Home page (blog list)
├── register.php            # Registration page
├── login.php               # Login page
├── create-blog.php         # Create blog page
├── edit-blog.php           # Edit blog page
├── view-blog.php           # Single blog view page
├── profile.php             # User profile page ✨
├── edit-profile.php        # Edit profile page ✨
├── install.php             # Initial database installation
├── update-database.php     # Database update script ✨
│
├── .env                    # Environment variables (not in Git)
├── .env.example            # Environment template
├── .gitignore             # Git ignore file
└── README.md              # This file
```

## 🚀 Usage

### Register a New Account
1. Click "Register" button
2. Fill in username, email, and password
3. Click "Register"

### Login
1. Click "Login" button
2. Enter your email and password
3. Click "Login"

### Create a Blog Post
1. After logging in, click "Create Blog"
2. Enter a title
3. Write content (Markdown supported)
4. Click "Publish Blog"

### View Your Profile
1. Click "My Profile" in the navigation
2. See your statistics, bio, and all your blog posts
3. Click "Edit Profile" to update your bio

### Edit Your Profile
1. Go to your profile page
2. Click "Edit Profile"
3. Update your username or add/edit your bio
4. Click "Update Profile"

### Like a Blog Post
1. Go to any blog post
2. Click the heart (❤️) button
3. Click again to unlike

### Comment on a Blog
1. Go to any blog post
2. Scroll to the comments section
3. Type your comment
4. Click "Post Comment"
5. Your comment appears instantly

### Delete Your Comment
1. Find your comment on any blog post
2. Click the "Delete" button next to your comment
3. Confirm deletion

### Markdown Support

The blog editor supports basic Markdown:

```markdown
# Heading 1
## Heading 2
### Heading 3

**bold text**
*italic text*

[link text](https://example.com)
```

### Edit a Blog Post
1. Go to your blog post (must be logged in as author)
2. Click "Edit Blog" button
3. Make changes
4. Click "Update Blog"

### Delete a Blog Post
1. Go to your blog post (must be logged in as author)
2. Click "Delete Blog" button
3. Confirm deletion

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using PDO prepared statements
- XSS protection using `htmlspecialchars()`
- Session-based authentication
- Authorization checks for edit/delete operations
- Input validation and sanitization
- CSRF protection through session validation

## 🗄️ Database Schema

### user Table
- `id` (INT, Primary Key, Auto Increment)
- `username` (VARCHAR 50, Unique)
- `email` (VARCHAR 100, Unique)
- `password` (VARCHAR 255, Hashed)
- `role` (VARCHAR 20, Default: 'user')
- `bio` (TEXT, Nullable) ✨
- `created_at` (TIMESTAMP)

### blog_post Table
- `id` (INT, Primary Key, Auto Increment)
- `user_id` (INT, Foreign Key → user.id)
- `title` (VARCHAR 255)
- `content` (TEXT)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

### comment Table ✨
- `id` (INT, Primary Key, Auto Increment)
- `blog_id` (INT, Foreign Key → blog_post.id)
- `user_id` (INT, Foreign Key → user.id)
- `content` (TEXT)
- `created_at` (TIMESTAMP)

### blog_like Table ✨
- `id` (INT, Primary Key, Auto Increment)
- `blog_id` (INT, Foreign Key → blog_post.id)
- `user_id` (INT, Foreign Key → user.id)
- `created_at` (TIMESTAMP)
- Unique constraint on (blog_id, user_id)

## 🌐 Hosting on Free Platforms

### InfinityFree

1. Sign up at [https://infinityfree.net/](https://infinityfree.net/)
2. Create a new account
3. Upload files via FTP or File Manager
4. Create MySQL database from cPanel
5. Update `.env` with hosting details:
   ```
   DB_HOST=sql123.infinityfree.net
   DB_NAME=if0_12345678_blog_db
   DB_USER=if0_12345678
   DB_PASS=your_password
   APP_URL=http://yourusername.rf.gd
   ```
6. Run `install.php` then `update-database.php` on hosted site
7. Access your site via provided URL

### 000webhost

1. Sign up at [https://www.000webhost.com/](https://www.000webhost.com/)
2. Create a website
3. Upload files via File Manager
4. Create MySQL database
5. Update `.env` with hosting details
6. Run installation scripts
7. Access your site

## 📝 Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_NAME` | Database name | `blog_db` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | `` |
| `APP_NAME` | Application name | `Blog Application` |
| `APP_URL` | Application URL | `http://localhost/blog-app` |
| `SESSION_NAME` | Session cookie name | `blog_session` |
| `SESSION_LIFETIME` | Session duration (seconds) | `3600` |

## 🐛 Troubleshooting

### Database Connection Error
- Check if MySQL is running in XAMPP
- Verify `.env` database credentials
- Ensure database `blog_db` exists

### Comment/Like Tables Don't Exist
- Run `update-database.php` to create new tables
- Or manually create tables using SQL provided above
- Restart MySQL in XAMPP

### 404 Error
- Check if files are in correct directory
- Verify Apache is running
- Check `APP_URL` in `.env` matches your local setup

### Session Issues
- Clear browser cookies
- Check PHP session configuration
- Restart Apache server

### Cannot Edit/Delete Blogs or Comments
- Ensure you're logged in
- Verify you're the blog/comment owner
- Check browser console for JavaScript errors

### Tablespace Errors
- Stop MySQL in XAMPP
- Delete orphaned `.ibd` files from `mysql/data/blog_db/`
- Start MySQL and recreate tables

## 🎨 Customization

### Change Color Scheme
Edit `assets/css/style.css`:
- Primary color: `#3498db`
- Secondary color: `#2c3e50`
- Accent color: `#e74c3c`

### Change App Name
Update `.env` file:
```
APP_NAME=Your Blog Name
```

### Modify Markdown Parser
Edit `includes/functions.php` → `markdownToHtml()` function

## 🚀 Future Enhancements

Potential features to add:
- Search functionality
- Blog categories/tags
- Pagination for blog lists
- Image upload for blogs
- Rich text editor
- Email notifications
- Password reset functionality
- Social media sharing
- Dark mode toggle
- Blog drafts
- Featured posts

## 📧 Contact

For any questions or issues, please contact:
- Email: your-isirigallage2002@gmail.com
- GitHub: Isiri-gallage

## 📄 License

This project is open source and available for educational purposes.

## 🙏 Acknowledgments

- University of Moratuwa - Faculty of Information Technology
- IN2120 - Web Programming Course

## 📊 Version History

### Version 2.0 (Latest) ✨
- Added user profile pages
- Implemented comments system
- Implemented likes functionality
- Added profile editing with bio
- Enhanced navigation with profile links
- Added real-time updates for comments and likes

### Version 1.0
- Initial release
- User authentication
- Blog CRUD operations
- Markdown support
- Responsive design

---

**Made with ❤️ for IN2120 Assignment**