from flask import Flask, render_template, request, redirect, url_for, flash, jsonify
from flask_sqlalchemy import SQLAlchemy
from flask_login import LoginManager, UserMixin, login_user, logout_user, login_required, current_user
from werkzeug.security import generate_password_hash, check_password_hash
from datetime import datetime
import os

app = Flask(__name__)
app.config['SECRET_KEY'] = 'your-secret-key-change-in-production'
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///dating_app.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

db = SQLAlchemy(app)
login_manager = LoginManager()
login_manager.init_app(app)
login_manager.login_view = 'login'

# Models
class User(UserMixin, db.Model):
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(80), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(128))
    name = db.Column(db.String(100), nullable=False)
    age = db.Column(db.Integer)
    bio = db.Column(db.Text)
    interests = db.Column(db.String(500))
    location = db.Column(db.String(100))
    profile_pic = db.Column(db.String(200), default='default.jpg')
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    def set_password(self, password):
        self.password_hash = generate_password_hash(password)
    
    def check_password(self, password):
        return check_password_hash(self.password_hash, password)

class Like(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    liker_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    liked_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
class Message(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    sender_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    receiver_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    content = db.Column(db.Text, nullable=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

# Routes
@app.route('/')
def index():
    if current_user.is_authenticated:
        return redirect(url_for('browse'))
    return render_template('index.html')

@app.route('/register', methods=['GET', 'POST'])
def register():
    if request.method == 'POST':
        username = request.form['username']
        email = request.form['email']
        password = request.form['password']
        name = request.form['name']
        age = request.form['age']
        
        if User.query.filter_by(username=username).first():
            flash('Username already exists')
            return render_template('register.html')
        
        if User.query.filter_by(email=email).first():
            flash('Email already exists')
            return render_template('register.html')
        
        user = User(username=username, email=email, name=name, age=int(age))
        user.set_password(password)
        db.session.add(user)
        db.session.commit()
        
        login_user(user)
        return redirect(url_for('profile_edit'))
    
    return render_template('register.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        user = User.query.filter_by(username=username).first()
        
        if user and user.check_password(password):
            login_user(user)
            return redirect(url_for('browse'))
        else:
            flash('Invalid username or password')
    
    return render_template('login.html')

@app.route('/logout')
@login_required
def logout():
    logout_user()
    return redirect(url_for('index'))

@app.route('/profile/edit', methods=['GET', 'POST'])
@login_required
def profile_edit():
    if request.method == 'POST':
        current_user.bio = request.form['bio']
        current_user.interests = request.form['interests']
        current_user.location = request.form['location']
        db.session.commit()
        flash('Profile updated successfully!')
        return redirect(url_for('browse'))
    
    return render_template('profile_edit.html')

@app.route('/browse')
@login_required
def browse():
    # Get users that current user hasn't liked yet
    liked_user_ids = [like.liked_id for like in Like.query.filter_by(liker_id=current_user.id).all()]
    liked_user_ids.append(current_user.id)  # Exclude self
    
    users = User.query.filter(~User.id.in_(liked_user_ids)).all()
    return render_template('browse.html', users=users)

@app.route('/like/<int:user_id>', methods=['POST'])
@login_required
def like_user(user_id):
    # Check if already liked
    existing_like = Like.query.filter_by(liker_id=current_user.id, liked_id=user_id).first()
    if not existing_like:
        like = Like(liker_id=current_user.id, liked_id=user_id)
        db.session.add(like)
        db.session.commit()
        
        # Check for mutual like
        mutual_like = Like.query.filter_by(liker_id=user_id, liked_id=current_user.id).first()
        if mutual_like:
            return jsonify({'status': 'match', 'message': "It's a match! 💕"})
    
    return jsonify({'status': 'liked', 'message': 'Liked! 👍'})

@app.route('/matches')
@login_required
def matches():
    # Find mutual likes
    my_likes = [like.liked_id for like in Like.query.filter_by(liker_id=current_user.id).all()]
    their_likes = [like.liker_id for like in Like.query.filter_by(liked_id=current_user.id).all()]
    
    matched_user_ids = list(set(my_likes) & set(their_likes))
    matched_users = User.query.filter(User.id.in_(matched_user_ids)).all()
    
    return render_template('matches.html', matches=matched_users)

@app.route('/chat/<int:user_id>')
@login_required
def chat(user_id):
    user = User.query.get_or_404(user_id)
    messages = Message.query.filter(
        ((Message.sender_id == current_user.id) & (Message.receiver_id == user_id)) |
        ((Message.sender_id == user_id) & (Message.receiver_id == current_user.id))
    ).order_by(Message.created_at).all()
    
    return render_template('chat.html', user=user, messages=messages)

@app.route('/send_message', methods=['POST'])
@login_required
def send_message():
    receiver_id = request.form['receiver_id']
    content = request.form['content']
    
    message = Message(sender_id=current_user.id, receiver_id=receiver_id, content=content)
    db.session.add(message)
    db.session.commit()
    
    return render_template('message.html', message=message, current_user=current_user)

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
    app.run(debug=True)