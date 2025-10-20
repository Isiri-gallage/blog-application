# Blog Application

A full-featured blog application built with PHP, MySQL, HTML, CSS, and JavaScript.

## 📋 Features

- **User Authentication**
  - User registration with validation
  - Secure login/logout system
  - Password hashing for security
  
- **Blog Management**
  - Create new blog posts with Markdown support
  - Read all blog posts on home page
  - Update your own blog posts
  - Delete your own blog posts
  - View individual blog posts
  
- **Authorization**
  - Only authenticated users can create blogs
  - Users can only edit/delete their own blogs
  - Protection against unauthorized access
  
- **Responsive Design**
  - Mobile-friendly interface
  - Clean and modern UI
  - Easy navigation

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

1. Navigate to `http://localhost/blog-app/install.php` (we'll create this file)
2. The tables will be created automatically
3. Or run this SQL manually in phpMyAdmin:

```sql
CREATE TABLE IF NOT EXISTS user (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS blog_post (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
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
│   └── delete-blog.php     # Delete blog endpoint
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
├── install.php             # Database installation script
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
1. Go to your blog post
2. Click "Edit Blog" button
3. Make changes
4. Click "Update Blog"

### Delete a Blog Post
1. Go to your blog post
2. Click "Delete Blog" button
3. Confirm deletion

## 🔒 Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention using PDO prepared statements
- XSS protection using `htmlspecialchars()`
- Session-based authentication
- Authorization checks for edit/delete operations
- Input validation and sanitization

## 🌐 Hosting on Free Platforms

### InfinityFree

1. Sign up at [https://infinityfree.net/](https://infinityfree.net/)
2. Create a new account
3. Upload files via FTP or File Manager
4. Create MySQL database from cPanel
5. Update `.env` with hosting details
6. Access your site via provided URL

### 000webhost

1. Sign up at [https://www.000webhost.com/](https://www.000webhost.com/)
2. Create a website
3. Upload files via File Manager
4. Create MySQL database
5. Update `.env` with hosting details
6. Access your site

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

### 404 Error
- Check if files are in correct directory
- Verify Apache is running
- Check `APP_URL` in `.env` matches your local setup

### Session Issues
- Clear browser cookies
- Check PHP session configuration
- Restart Apache server

### Cannot Edit/Delete Blogs
- Ensure you're logged in
- Verify you're the blog owner
- Check browser console for JavaScript errors

## 📧 Contact

For any questions or issues, please contact:
- Email: your-email@example.com
- GitHub: your-github-username

## 📄 License

This project is open source and available for educational purposes.

## 🙏 Acknowledgments

- University of Moratuwa - Faculty of Information Technology
- IN2120 - Web Programming Course