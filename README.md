# 💕 LoveConnect - Modern Dating App

A beautiful, modern dating application built with Flask & HTMX featuring a colorful, engaging user interface.

![LoveConnect Homepage](https://github.com/user-attachments/assets/26e5770a-7d70-4db9-891a-9dc072d6dc2f)

## ✨ Features

- 🎨 **Colorful UI**: Beautiful gradient backgrounds and modern design
- 💝 **Interactive Elements**: Floating hearts, pulse animations, and smooth transitions
- 🔐 **User Authentication**: Secure registration and login system
- 👤 **Profile Management**: Create and edit detailed user profiles
- 🔍 **Profile Browsing**: Swipe-style interface to discover new people
- ❤️ **Matching System**: Like profiles and get notified of mutual matches
- 💬 **Real-time Chat**: HTMX-powered messaging system for matched users
- 📱 **Responsive Design**: Works perfectly on all devices

## 🚀 Quick Start

1. **Install dependencies:**
   ```bash
   pip install -r requirements.txt
   ```

2. **Run the application:**
   ```bash
   python app.py
   ```

3. **Open your browser and visit:**
   ```
   http://127.0.0.1:5000
   ```

## 🛠️ Tech Stack

- **Backend**: Flask 2.3.3 with SQLAlchemy
- **Frontend**: HTML5, CSS3, Bootstrap 5.1.3
- **Dynamic Interactions**: HTMX 1.9.6
- **Database**: SQLite (development)
- **Authentication**: Flask-Login

## 📱 App Flow

1. **Homepage** - Welcome screen with colorful design
2. **Registration** - Create your account
3. **Profile Setup** - Add your bio, interests, and location
4. **Browse Profiles** - Discover other users
5. **Like & Match** - Show interest and get matched
6. **Chat** - Start conversations with your matches

## 🎨 Design Features

- **Gradient Backgrounds**: Purple to blue gradients throughout the app
- **Colorful Buttons**: Pink and blue gradient buttons with hover effects
- **Floating Hearts**: Animated heart elements for romantic atmosphere
- **Glass-morphism Cards**: Modern frosted glass effect on cards
- **Smooth Animations**: CSS transitions and keyframe animations

## 📁 Project Structure

```
onlinedating/
├── app.py                 # Main Flask application
├── requirements.txt       # Python dependencies
├── static/
│   └── css/
│       └── style.css     # Custom styling
└── templates/
    ├── base.html         # Base template
    ├── index.html        # Homepage
    ├── register.html     # Registration form
    ├── login.html        # Login form
    ├── profile_edit.html # Profile editing
    ├── browse.html       # Profile browsing
    ├── matches.html      # Matched users
    ├── chat.html         # Chat interface
    └── message.html      # Message component
```

## 🔧 Development

The application uses:
- **Flask** for the web framework
- **SQLAlchemy** for database operations
- **HTMX** for dynamic, AJAX-like interactions without JavaScript
- **Bootstrap** for responsive layout
- **Custom CSS** for unique colorful styling

## 🌟 Key Improvements Over Original

This is a complete rewrite from the original PHP application, featuring:
- Modern Python web framework (Flask)
- Responsive, colorful design
- Real-time interactions with HTMX
- Proper database modeling
- Secure authentication
- Mobile-friendly interface

## 📄 License

This project is open source and available under the MIT License.
