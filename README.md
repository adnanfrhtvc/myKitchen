# myKitchen - Software Engineering Project by Adnan Ferhatović and Alen Kuršumlija

A comprehensive web-based kitchen productivity application built for a university software engineering course. The app helps users manage their kitchen activities including recipes, tasks, grocery lists, meal planning, and account management.

## 🎯 Overview

myKitchen is a full-stack web application designed to streamline kitchen management and meal planning. Built using the MVC (Model-View-Controller) architecture pattern with PHP backend and vanilla JavaScript frontend, it provides a comprehensive solution for home cooks and kitchen enthusiasts.

## ✨ Features

### 🔐 User Management
- **User Registration & Login**: Secure account creation and authentication
- **Password Reset**: Email-based password recovery system
- **Profile Management**: Update personal information and account settings
- **Account Deletion**: Complete account removal with data cleanup

### 📝 Task Management
- **CRUD Operations**: Create, read, update, and delete tasks
- **Priority Levels**: Low, medium, and high priority classification
- **Status Tracking**: Pending, in progress, and completed states
- **Due Date Management**: Set and track task deadlines
- **Task Dashboard**: Quick overview of recent and important tasks

### 🍳 Recipe Management
- **Recipe Creation**: Add detailed recipes with ingredients and instructions
- **Cooking Information**: Prep time, cook time, and serving size tracking
- **Difficulty Levels**: Easy, medium, and hard classification
- **Recipe Organization**: Search and categorize personal recipe collection
- **Detailed View**: Complete recipe information display

### 🛒 Grocery List Management
- **Smart Lists**: Create and manage grocery shopping lists
- **Quick Add**: Fast item addition with quantity tracking
- **Completion Tracking**: Check off items as you shop
- **Bulk Operations**: Clear completed items in one action
- **Dashboard Integration**: Quick view of grocery items

### 📊 Dashboard
- **Unified Overview**: Central hub for all kitchen activities
- **Recent Items**: Quick access to latest tasks and recipes
- **Grocery Preview**: Snapshot of current shopping list
- **Responsive Layout**: Optimized for desktop and mobile viewing

### 📱 Mobile Responsive Design
- **Hamburger Navigation**: Mobile-friendly menu system
- **Touch-Optimized**: Designed for mobile interaction
- **Responsive Layouts**: Adapts to all screen sizes
- **Progressive Enhancement**: Works on all devices

## 🛠 Technologies Used

### Frontend
- **HTML5**: Semantic markup and structure
- **CSS3**: Modern styling with Flexbox and Grid
- **JavaScript (ES6+)**: Vanilla JavaScript for interactivity
- **Responsive Design**: Mobile-first approach

### Backend
- **PHP**: Server-side logic and API endpoints
- **MySQL**: Relational database management
- **Apache**: Web server (via XAMPP)
- **PDO**: Database abstraction layer

### Development Tools
- **XAMPP**: Local development environment
- **phpMyAdmin**: Database administration
- **Git**: Version control
- **VS Code**: Recommended IDE

## 🏗 Architecture & Design Patterns

### MVC Architecture
\`\`\`
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│     Models      │    │   Controllers   │    │     Views       │
│                 │    │                 │    │                 │
│ • User.php      │◄──►│ • AuthController│◄──►│ • index.html    │
│ • Task.php      │    │ • TaskController│    │ • CSS Styles    │
│ • Recipe.php    │    │ • RecipeController   │ • JavaScript    │
│ • GroceryList   │    │ • GroceryController  │                 │
└─────────────────┘    └─────────────────┘    └─────────────────┘
\`\`\`

### Design Patterns Implemented

#### 1. **Factory Pattern**
\`\`\`php
// EntityFactory.php
$user = EntityFactory::create('user');
$task = EntityFactory::create('task', $data);
\`\`\`
- **Purpose**: Dynamic entity creation
- **Benefits**: Centralized object instantiation, easy to extend

#### 2. **Singleton Pattern**
\`\`\`php
// Database.php
$db = Database::getInstance()->getConnection();
\`\`\`
- **Purpose**: Single database connection instance
- **Benefits**: Resource management, prevents connection overhead

### Usage

1. **Create Account**
   - Click "Register here" on login page
   - Fill in required information
   - Verify account creation

2. **Dashboard Overview**
   - View recent tasks and recipes
   - Check grocery list preview
   - Navigate to different sections

3. **Managing Tasks**
   - Click "Add Task" to create new tasks
   - Set priority levels and due dates
   - Update status as you progress
   - Edit or delete existing tasks

4. **Recipe Management**
   - Add recipes with detailed ingredients
   - Include cooking instructions and times
   - Set difficulty levels and serving sizes
   - Organize your recipe collection

5. **Grocery Lists**
   - Quick-add items with quantities
   - Check off items while shopping
   - Edit items as needed
   - Clear completed items in bulk

### 📱 Mobile Usage
- Tap the hamburger menu (☰) for navigation
- All features are touch-optimized
- Responsive design adapts to your screen
- Works offline for viewing existing data
