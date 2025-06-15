/**
 * myKitchen Frontend Application
 * Handles all client-side functionality
 */

class MyKitchenApp {
  constructor() {
    this.currentUser = null
    this.currentPage = "login"
    this.editingTask = null
    this.editingRecipe = null
    this.editingGroceryItem = null

    this.init()
  }

  init() {
    this.bindEvents()
    this.checkAuthentication()
  }

  bindEvents() {
    // Authentication events
    document.getElementById("login-form").addEventListener("submit", (e) => this.handleLogin(e))
    document.getElementById("register-form").addEventListener("submit", (e) => this.handleRegister(e))
    document.getElementById("show-register").addEventListener("click", (e) => this.showRegister(e))
    document.getElementById("show-login").addEventListener("click", (e) => this.showLogin(e))
    document.getElementById("logout-btn").addEventListener("click", (e) => this.handleLogout(e))

    // Navigation events
    document.querySelectorAll(".nav-link[data-page]").forEach((link) => {
      link.addEventListener("click", (e) => this.navigateTo(e))
    })

    // Mobile navigation toggle
    document.getElementById("nav-toggle").addEventListener("click", () => this.toggleMobileNav())

    // Task events
    document.getElementById("add-task-btn").addEventListener("click", () => this.showTaskModal())
    document.getElementById("task-form").addEventListener("submit", (e) => this.handleTaskSubmit(e))
    document.getElementById("close-task-modal").addEventListener("click", () => this.hideTaskModal())
    document.getElementById("cancel-task").addEventListener("click", () => this.hideTaskModal())

    // Recipe events
    document.getElementById("add-recipe-btn").addEventListener("click", () => this.showRecipeModal())
    document.getElementById("recipe-form").addEventListener("submit", (e) => this.handleRecipeSubmit(e))
    document.getElementById("close-recipe-modal").addEventListener("click", () => this.hideRecipeModal())
    document.getElementById("cancel-recipe").addEventListener("click", () => this.hideRecipeModal())

    // Modal backdrop clicks
    document.getElementById("task-modal").addEventListener("click", (e) => {
      if (e.target.id === "task-modal") this.hideTaskModal()
    })
    document.getElementById("recipe-modal").addEventListener("click", (e) => {
      if (e.target.id === "recipe-modal") this.hideRecipeModal()
    })

    // Password reset events
    document.getElementById("show-reset").addEventListener("click", (e) => this.showReset(e))
    document.getElementById("back-to-login").addEventListener("click", (e) => this.showLogin(e))
    document.getElementById("reset-form").addEventListener("submit", (e) => this.handlePasswordReset(e))

    // Account events
    document.getElementById("profile-form").addEventListener("submit", (e) => this.handleProfileUpdate(e))
    document.getElementById("delete-account-btn").addEventListener("click", (e) => this.handleDeleteAccount(e))

    // Grocery events
    document.getElementById("add-grocery-btn").addEventListener("click", () => this.showGroceryModal())
    document.getElementById("quick-add-form").addEventListener("submit", (e) => this.handleQuickAdd(e))
    document.getElementById("grocery-form").addEventListener("submit", (e) => this.handleGrocerySubmit(e))
    document.getElementById("close-grocery-modal").addEventListener("click", () => this.hideGroceryModal())
    document.getElementById("cancel-grocery").addEventListener("click", () => this.hideGroceryModal())
    document.getElementById("clear-completed-btn").addEventListener("click", () => this.clearCompleted())

    // Grocery modal backdrop click
    document.getElementById("grocery-modal").addEventListener("click", (e) => {
      if (e.target.id === "grocery-modal") this.hideGroceryModal()
    })
  }

  async checkAuthentication() {
    try {
      const response = await fetch("../backend/controllers/AuthController.php?action=check")
      const data = await response.json()

      if (data.authenticated) {
        this.currentUser = data.user
        this.showDashboard()
      } else {
        this.showLogin()
      }
    } catch (error) {
      console.error("Auth check failed:", error)
      this.showLogin()
    }
  }

  async handleLogin(e) {
    e.preventDefault()

    const email = document.getElementById("login-email").value
    const password = document.getElementById("login-password").value

    try {
      const response = await fetch("../backend/controllers/AuthController.php?action=login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
      })

      const data = await response.json()

      if (response.ok) {
        this.currentUser = data.user
        this.showDashboard()
        this.showSuccess("Login successful!")
      } else {
        this.showError(data.error || "Login failed")
      }
    } catch (error) {
      console.error("Login error:", error)
      this.showError("Login failed. Please try again.")
    }
  }

  async handleRegister(e) {
    e.preventDefault()

    const formData = {
      username: document.getElementById("register-username").value,
      email: document.getElementById("register-email").value,
      first_name: document.getElementById("register-first-name").value,
      last_name: document.getElementById("register-last-name").value,
      password: document.getElementById("register-password").value,
    }

    try {
      const response = await fetch("../backend/controllers/AuthController.php?action=register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      })

      const data = await response.json()

      if (response.ok) {
        this.showSuccess("Registration successful! Please login.")
        this.showLogin()
      } else {
        this.showError(data.error || "Registration failed")
      }
    } catch (error) {
      console.error("Registration error:", error)
      this.showError("Registration failed. Please try again.")
    }
  }

  async handleLogout(e) {
    e.preventDefault()

    try {
      await fetch("../backend/controllers/AuthController.php?action=logout")
      this.currentUser = null
      this.showLogin()
      this.showSuccess("Logged out successfully!")
    } catch (error) {
      console.error("Logout error:", error)
    }
  }

  showLogin(e) {
    if (e) e.preventDefault()
    this.showPage("login")
    document.getElementById("navbar").classList.remove("show")
    document.querySelector(".main-content").classList.remove("with-nav")
  }

  showRegister(e) {
    if (e) e.preventDefault()
    this.showPage("register")
  }

  showDashboard() {
    this.showPage("dashboard")
    document.getElementById("navbar").classList.add("show")
    document.querySelector(".main-content").classList.add("with-nav")
    this.loadDashboardData()
  }

  navigateTo(e) {
    e.preventDefault()
    const page = e.target.getAttribute("data-page")

    // Close mobile menu if open
    this.closeMobileNav()

    this.showPage(page)

    if (page === "tasks") {
      this.loadTasks()
    } else if (page === "recipes") {
      this.loadRecipes()
    } else if (page === "dashboard") {
      this.loadDashboardData()
    } else if (page === "grocery") {
      this.loadGroceryList()
    } else if (page === "account") {
      this.loadAccountData()
    }
  }

  showPage(pageId) {
    document.querySelectorAll(".page").forEach((page) => {
      page.classList.remove("active")
    })
    document.getElementById(`${pageId}-page`).classList.add("active")
    this.currentPage = pageId
  }

  async loadDashboardData() {
    try {
      // Load recent tasks
      const tasksResponse = await fetch("../backend/controllers/TaskController.php?action=getAll")
      const tasks = await tasksResponse.json()
      this.renderRecentTasks(tasks.slice(0, 3))

      // Load recent recipes
      const recipesResponse = await fetch("../backend/controllers/RecipeController.php?action=getAll")
      const recipes = await recipesResponse.json()
      this.renderRecentRecipes(recipes.slice(0, 3))

      // Load grocery list for dashboard
      const groceryResponse = await fetch("../backend/controllers/GroceryController.php?action=getAll")
      const groceryItems = await groceryResponse.json()
      this.renderDashboardGrocery(groceryItems.slice(0, 8))
    } catch (error) {
      console.error("Dashboard load error:", error)
    }
  }

  renderRecentTasks(tasks) {
    const container = document.getElementById("recent-tasks")

    if (tasks.length === 0) {
      container.innerHTML = '<p class="empty-state">No tasks yet.</p>'
      return
    }

    container.innerHTML = tasks
      .map(
        (task) => `
          <div class="task-item">
              <h4>${task.title}</h4>
              <span class="priority-${task.priority}">${task.priority}</span>
              <span class="status-${task.status}">${task.status.replace("_", " ")}</span>
          </div>
      `,
      )
      .join("")
  }

  renderRecentRecipes(recipes) {
    const container = document.getElementById("recent-recipes")

    if (recipes.length === 0) {
      container.innerHTML = '<p class="empty-state">No recipes yet.</p>'
      return
    }

    container.innerHTML = recipes
      .map(
        (recipe) => `
          <div class="recipe-item">
              <h4>${recipe.title}</h4>
              <p>${recipe.description || "No description"}</p>
              <small>Difficulty: ${recipe.difficulty}</small>
          </div>
      `,
      )
      .join("")
  }

  async loadTasks() {
    try {
      const response = await fetch("../backend/controllers/TaskController.php?action=getAll")
      const tasks = await response.json()
      this.renderTasks(tasks)
    } catch (error) {
      console.error("Tasks load error:", error)
      this.showError("Failed to load tasks")
    }
  }

  renderTasks(tasks) {
    const container = document.getElementById("tasks-container")

    if (tasks.length === 0) {
      container.innerHTML = `
                <div class="empty-state">
                    <h3>No tasks yet</h3>
                    <p>Create your first task to get started!</p>
                </div>
            `
      return
    }

    container.innerHTML = tasks
      .map(
        (task) => `
            <div class="task-card">
                <h3>${task.title}</h3>
                <p>${task.description || "No description"}</p>
                <div class="task-meta">
                    <span class="priority-${task.priority}">${task.priority}</span>
                    <span class="status-${task.status}">${task.status.replace("_", " ")}</span>
                    ${task.due_date ? `<span>Due: ${new Date(task.due_date).toLocaleDateString()}</span>` : ""}
                </div>
                <div class="task-actions">
                    <button class="btn btn-secondary" onclick="app.editTask(${task.id})">Edit</button>
                    <button class="btn btn-danger" onclick="app.deleteTask(${task.id})">Delete</button>
                </div>
            </div>
        `,
      )
      .join("")
  }

  async loadRecipes() {
    try {
      const response = await fetch("../backend/controllers/RecipeController.php?action=getAll")
      const recipes = await response.json()
      this.renderRecipes(recipes)
    } catch (error) {
      console.error("Recipes load error:", error)
      this.showError("Failed to load recipes")
    }
  }

  renderRecipes(recipes) {
    const container = document.getElementById("recipes-container")

    if (recipes.length === 0) {
      container.innerHTML = `
                <div class="empty-state">
                    <h3>No recipes yet</h3>
                    <p>Add your first recipe to get started!</p>
                </div>
            `
      return
    }

    container.innerHTML = recipes
      .map(
        (recipe) => `
            <div class="recipe-card">
                <h3>${recipe.title}</h3>
                <p>${recipe.description || "No description"}</p>
                <div class="recipe-meta">
                    <span>Prep: ${recipe.prep_time || 0}min</span>
                    <span>Cook: ${recipe.cook_time || 0}min</span>
                    <span>Serves: ${recipe.servings}</span>
                    <span class="difficulty-${recipe.difficulty}">${recipe.difficulty}</span>
                </div>
                <div class="recipe-actions">
                    <button class="btn btn-secondary" onclick="app.editRecipe(${recipe.id})">Edit</button>
                    <button class="btn btn-danger" onclick="app.deleteRecipe(${recipe.id})">Delete</button>
                </div>
            </div>
        `,
      )
      .join("")
  }

  // Task Modal Methods
  showTaskModal(task = null) {
    this.editingTask = task
    const modal = document.getElementById("task-modal")
    const title = document.getElementById("task-modal-title")

    if (task) {
      title.textContent = "Edit Task"
      this.populateTaskForm(task)
    } else {
      title.textContent = "Add Task"
      this.clearTaskForm()
    }

    modal.classList.add("show")
  }

  hideTaskModal() {
    document.getElementById("task-modal").classList.remove("show")
    this.editingTask = null
    this.clearTaskForm()
  }

  populateTaskForm(task) {
    document.getElementById("task-title").value = task.title
    document.getElementById("task-description").value = task.description || ""
    document.getElementById("task-priority").value = task.priority
    document.getElementById("task-status").value = task.status

    if (task.due_date) {
      const date = new Date(task.due_date)
      document.getElementById("task-due-date").value = date.toISOString().slice(0, 16)
    }
  }

  clearTaskForm() {
    document.getElementById("task-form").reset()
    document.getElementById("task-priority").value = "medium"
    document.getElementById("task-status").value = "pending"
  }

  async handleTaskSubmit(e) {
    e.preventDefault()

    const formData = {
      title: document.getElementById("task-title").value,
      description: document.getElementById("task-description").value,
      priority: document.getElementById("task-priority").value,
      status: document.getElementById("task-status").value,
      due_date: document.getElementById("task-due-date").value || null,
    }

    try {
      let response
      if (this.editingTask) {
        response = await fetch(`../backend/controllers/TaskController.php?action=update&id=${this.editingTask.id}`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })
      } else {
        response = await fetch("../backend/controllers/TaskController.php?action=create", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })
      }

      const data = await response.json()

      if (response.ok) {
        this.hideTaskModal()
        this.showSuccess(this.editingTask ? "Task updated successfully!" : "Task created successfully!")
        if (this.currentPage === "tasks") {
          this.loadTasks()
        }
      } else {
        this.showError(data.error || "Failed to save task")
      }
    } catch (error) {
      console.error("Task save error:", error)
      this.showError("Failed to save task. Please try again.")
    }
  }

  async editTask(id) {
    try {
      const response = await fetch(`../backend/controllers/TaskController.php?action=get&id=${id}`)
      const task = await response.json()

      if (response.ok) {
        this.showTaskModal(task)
      } else {
        this.showError("Failed to load task")
      }
    } catch (error) {
      console.error("Task load error:", error)
      this.showError("Failed to load task")
    }
  }

  async deleteTask(id) {
    if (!confirm("Are you sure you want to delete this task?")) {
      return
    }

    try {
      const response = await fetch(`../backend/controllers/TaskController.php?action=delete&id=${id}`, {
        method: "DELETE",
      })

      const data = await response.json()

      if (response.ok) {
        this.showSuccess("Task deleted successfully!")
        this.loadTasks()
      } else {
        this.showError(data.error || "Failed to delete task")
      }
    } catch (error) {
      console.error("Task delete error:", error)
      this.showError("Failed to delete task")
    }
  }

  // Recipe Modal Methods
  showRecipeModal(recipe = null) {
    this.editingRecipe = recipe
    const modal = document.getElementById("recipe-modal")
    const title = document.getElementById("recipe-modal-title")

    if (recipe) {
      title.textContent = "Edit Recipe"
      this.populateRecipeForm(recipe)
    } else {
      title.textContent = "Add Recipe"
      this.clearRecipeForm()
    }

    modal.classList.add("show")
  }

  hideRecipeModal() {
    document.getElementById("recipe-modal").classList.remove("show")
    this.editingRecipe = null
    this.clearRecipeForm()
  }

  populateRecipeForm(recipe) {
    document.getElementById("recipe-title").value = recipe.title
    document.getElementById("recipe-description").value = recipe.description || ""
    document.getElementById("recipe-ingredients").value = recipe.ingredients
    document.getElementById("recipe-instructions").value = recipe.instructions
    document.getElementById("recipe-prep-time").value = recipe.prep_time || ""
    document.getElementById("recipe-cook-time").value = recipe.cook_time || ""
    document.getElementById("recipe-servings").value = recipe.servings
    document.getElementById("recipe-difficulty").value = recipe.difficulty
  }

  clearRecipeForm() {
    document.getElementById("recipe-form").reset()
    document.getElementById("recipe-servings").value = "1"
    document.getElementById("recipe-difficulty").value = "medium"
  }

  async handleRecipeSubmit(e) {
    e.preventDefault()

    const formData = {
      title: document.getElementById("recipe-title").value,
      description: document.getElementById("recipe-description").value,
      ingredients: document.getElementById("recipe-ingredients").value,
      instructions: document.getElementById("recipe-instructions").value,
      prep_time: document.getElementById("recipe-prep-time").value || null,
      cook_time: document.getElementById("recipe-cook-time").value || null,
      servings: document.getElementById("recipe-servings").value,
      difficulty: document.getElementById("recipe-difficulty").value,
    }

    try {
      let response
      if (this.editingRecipe) {
        response = await fetch(
          `../backend/controllers/RecipeController.php?action=update&id=${this.editingRecipe.id}`,
          {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(formData),
          },
        )
      } else {
        response = await fetch("../backend/controllers/RecipeController.php?action=create", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })
      }

      const data = await response.json()

      if (response.ok) {
        this.hideRecipeModal()
        this.showSuccess(this.editingRecipe ? "Recipe updated successfully!" : "Recipe created successfully!")
        if (this.currentPage === "recipes") {
          this.loadRecipes()
        }
      } else {
        this.showError(data.error || "Failed to save recipe")
      }
    } catch (error) {
      console.error("Recipe save error:", error)
      this.showError("Failed to save recipe. Please try again.")
    }
  }

  async editRecipe(id) {
    try {
      const response = await fetch(`../backend/controllers/RecipeController.php?action=get&id=${id}`)
      const recipe = await response.json()

      if (response.ok) {
        this.showRecipeModal(recipe)
      } else {
        this.showError("Failed to load recipe")
      }
    } catch (error) {
      console.error("Recipe load error:", error)
      this.showError("Failed to load recipe")
    }
  }

  async deleteRecipe(id) {
    if (!confirm("Are you sure you want to delete this recipe?")) {
      return
    }

    try {
      const response = await fetch(`../backend/controllers/RecipeController.php?action=delete&id=${id}`, {
        method: "DELETE",
      })

      const data = await response.json()

      if (response.ok) {
        this.showSuccess("Recipe deleted successfully!")
        this.loadRecipes()
      } else {
        this.showError(data.error || "Failed to delete recipe")
      }
    } catch (error) {
      console.error("Recipe delete error:", error)
      this.showError("Failed to delete recipe")
    }
  }

  // Password Reset Methods
  showReset(e) {
    if (e) e.preventDefault()
    this.showPage("reset")
  }

  async handlePasswordReset(e) {
    e.preventDefault()

    const email = document.getElementById("reset-email").value
    const newPassword = document.getElementById("reset-new-password").value
    const confirmPassword = document.getElementById("reset-confirm-password").value

    if (newPassword !== confirmPassword) {
      this.showError("Passwords do not match")
      return
    }

    if (newPassword.length < 6) {
      this.showError("Password must be at least 6 characters long")
      return
    }

    try {
      const response = await fetch("../backend/controllers/AuthController.php?action=resetPassword", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, new_password: newPassword }),
      })

      const data = await response.json()

      if (response.ok) {
        this.showSuccess("Password reset successfully! Please login with your new password.")
        this.showLogin()
      } else {
        this.showError(data.error || "Password reset failed")
      }
    } catch (error) {
      console.error("Password reset error:", error)
      this.showError("Password reset failed. Please try again.")
    }
  }

  // Account Management Methods
  async loadAccountData() {
    if (this.currentUser) {
      document.getElementById("profile-username").value = this.currentUser.username
      document.getElementById("profile-email").value = this.currentUser.email
      document.getElementById("profile-first-name").value = this.currentUser.first_name || ""
      document.getElementById("profile-last-name").value = this.currentUser.last_name || ""
    }
  }

  async handleProfileUpdate(e) {
    e.preventDefault()

    const formData = {
      username: document.getElementById("profile-username").value,
      first_name: document.getElementById("profile-first-name").value,
      last_name: document.getElementById("profile-last-name").value,
    }

    try {
      const response = await fetch("../backend/controllers/AuthController.php?action=updateProfile", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      })

      const data = await response.json()

      if (response.ok) {
        this.currentUser.username = formData.username
        this.currentUser.first_name = formData.first_name
        this.currentUser.last_name = formData.last_name
        this.showSuccess("Profile updated successfully!")
      } else {
        this.showError(data.error || "Failed to update profile")
      }
    } catch (error) {
      console.error("Profile update error:", error)
      this.showError("Failed to update profile. Please try again.")
    }
  }

  async handleDeleteAccount(e) {
    e.preventDefault()

    if (!confirm("Are you sure you want to delete your account? This action cannot be undone.")) {
      return
    }

    if (!confirm("This will permanently delete all your data. Are you absolutely sure?")) {
      return
    }

    try {
      const response = await fetch("../backend/controllers/AuthController.php?action=deleteAccount", {
        method: "DELETE",
      })

      const data = await response.json()

      if (response.ok) {
        this.showSuccess("Account deleted successfully!")
        this.currentUser = null
        this.showLogin()
      } else {
        this.showError(data.error || "Failed to delete account")
      }
    } catch (error) {
      console.error("Account deletion error:", error)
      this.showError("Failed to delete account. Please try again.")
    }
  }

  // Grocery List Methods
  async loadGroceryList() {
    try {
      const response = await fetch("../backend/controllers/GroceryController.php?action=getAll")
      const items = await response.json()
      this.renderGroceryList(items)
    } catch (error) {
      console.error("Grocery list load error:", error)
      this.showError("Failed to load grocery list")
    }
  }

  renderGroceryList(items) {
    const container = document.getElementById("grocery-list")

    if (items.length === 0) {
      container.innerHTML = `
      <div class="grocery-empty">
        <h3>Your grocery list is empty</h3>
        <p>Add items using the form above or the "Add Item" button!</p>
      </div>
    `
      return
    }

    container.innerHTML = items
      .map(
        (item) => `
      <div class="grocery-item ${item.is_completed ? "completed" : ""}">
        <input type="checkbox" class="grocery-checkbox" 
               ${item.is_completed ? "checked" : ""} 
               onchange="app.toggleGroceryItem(${item.id})">
        <div class="grocery-item-content">
          <div class="grocery-item-name">${item.item_name}</div>
          ${item.quantity ? `<div class="grocery-item-quantity">${item.quantity}</div>` : ""}
        </div>
        <div class="grocery-item-actions">
          <button class="btn btn-secondary" onclick="app.editGroceryItem(${item.id})">Edit</button>
          <button class="btn btn-danger" onclick="app.deleteGroceryItem(${item.id})">Delete</button>
        </div>
      </div>
    `,
      )
      .join("")
  }

  async handleQuickAdd(e) {
    e.preventDefault()

    const itemName = document.getElementById("quick-item-name").value
    const quantity = document.getElementById("quick-quantity").value

    const formData = {
      item_name: itemName,
      quantity: quantity || null,
    }

    try {
      const response = await fetch("../backend/controllers/GroceryController.php?action=create", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData),
      })

      const data = await response.json()

      if (response.ok) {
        document.getElementById("quick-add-form").reset()
        this.loadGroceryList()
      } else {
        this.showError(data.error || "Failed to add item")
      }
    } catch (error) {
      console.error("Quick add error:", error)
      this.showError("Failed to add item. Please try again.")
    }
  }

  showGroceryModal(item = null) {
    this.editingGroceryItem = item
    const modal = document.getElementById("grocery-modal")
    const title = document.getElementById("grocery-modal-title")

    if (item) {
      title.textContent = "Edit Grocery Item"
      this.populateGroceryForm(item)
    } else {
      title.textContent = "Add Grocery Item"
      this.clearGroceryForm()
    }

    modal.classList.add("show")
  }

  hideGroceryModal() {
    document.getElementById("grocery-modal").classList.remove("show")
    this.editingGroceryItem = null
    this.clearGroceryForm()
  }

  populateGroceryForm(item) {
    document.getElementById("grocery-item-name").value = item.item_name
    document.getElementById("grocery-quantity").value = item.quantity || ""
  }

  clearGroceryForm() {
    document.getElementById("grocery-form").reset()
  }

  async handleGrocerySubmit(e) {
    e.preventDefault()

    const formData = {
      item_name: document.getElementById("grocery-item-name").value,
      quantity: document.getElementById("grocery-quantity").value || null,
      is_completed: this.editingGroceryItem ? this.editingGroceryItem.is_completed : false,
    }

    try {
      let response
      if (this.editingGroceryItem) {
        response = await fetch(
          `../backend/controllers/GroceryController.php?action=update&id=${this.editingGroceryItem.id}`,
          {
            method: "PUT",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify(formData),
          },
        )
      } else {
        response = await fetch("../backend/controllers/GroceryController.php?action=create", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(formData),
        })
      }

      const data = await response.json()

      if (response.ok) {
        this.hideGroceryModal()
        this.showSuccess(this.editingGroceryItem ? "Item updated successfully!" : "Item added successfully!")
        this.loadGroceryList()
      } else {
        this.showError(data.error || "Failed to save item")
      }
    } catch (error) {
      console.error("Grocery save error:", error)
      this.showError("Failed to save item. Please try again.")
    }
  }

  async toggleGroceryItem(id) {
    try {
      const response = await fetch(`../backend/controllers/GroceryController.php?action=toggle&id=${id}`, {
        method: "PUT",
      })

      const data = await response.json()

      if (response.ok) {
        this.loadGroceryList()
      } else {
        this.showError(data.error || "Failed to update item")
      }
    } catch (error) {
      console.error("Toggle error:", error)
      this.showError("Failed to update item")
    }
  }

  async editGroceryItem(id) {
    try {
      const response = await fetch(`../backend/controllers/GroceryController.php?action=get&id=${id}`)
      const item = await response.json()

      if (response.ok) {
        this.showGroceryModal(item)
      } else {
        this.showError("Failed to load item")
      }
    } catch (error) {
      console.error("Item load error:", error)
      this.showError("Failed to load item")
    }
  }

  async deleteGroceryItem(id) {
    if (!confirm("Are you sure you want to delete this item?")) {
      return
    }

    try {
      const response = await fetch(`../backend/controllers/GroceryController.php?action=delete&id=${id}`, {
        method: "DELETE",
      })

      const data = await response.json()

      if (response.ok) {
        this.loadGroceryList()
      } else {
        this.showError(data.error || "Failed to delete item")
      }
    } catch (error) {
      console.error("Delete error:", error)
      this.showError("Failed to delete item")
    }
  }

  async clearCompleted() {
    if (!confirm("Are you sure you want to clear all completed items?")) {
      return
    }

    try {
      const response = await fetch("../backend/controllers/GroceryController.php?action=clearCompleted", {
        method: "DELETE",
      })

      const data = await response.json()

      if (response.ok) {
        this.showSuccess("Completed items cleared!")
        this.loadGroceryList()
      } else {
        this.showError(data.error || "Failed to clear completed items")
      }
    } catch (error) {
      console.error("Clear completed error:", error)
      this.showError("Failed to clear completed items")
    }
  }

  // Mobile Navigation Methods
  toggleMobileNav() {
    const navMenu = document.getElementById("nav-menu")
    const navToggle = document.getElementById("nav-toggle")

    navMenu.classList.toggle("active")
    navToggle.classList.toggle("active")
  }

  closeMobileNav() {
    const navMenu = document.getElementById("nav-menu")
    const navToggle = document.getElementById("nav-toggle")

    navMenu.classList.remove("active")
    navToggle.classList.remove("active")
  }

  // Dashboard Grocery List
  renderDashboardGrocery(items) {
    const container = document.getElementById("dashboard-grocery")

    if (items.length === 0) {
      container.innerHTML = '<p class="empty-state">No grocery items yet.</p>'
      return
    }

    container.innerHTML = items
      .map(
        (item) => `
          <div class="dashboard-grocery-item ${item.is_completed ? "completed" : ""}">
            <input type="checkbox" class="dashboard-grocery-checkbox" 
                   ${item.is_completed ? "checked" : ""} 
                   onchange="app.toggleGroceryItem(${item.id})">
            <span class="grocery-name">${item.item_name}</span>
            ${item.quantity ? `<span class="grocery-quantity">(${item.quantity})</span>` : ""}
          </div>
        `,
      )
      .join("")
  }

  // Utility Methods
  showError(message) {
    this.showNotification(message, "error")
  }

  showSuccess(message) {
    this.showNotification(message, "success")
  }

  showNotification(message, type) {
    // Remove existing notifications
    const existing = document.querySelector(".notification")
    if (existing) {
      existing.remove()
    }

    const notification = document.createElement("div")
    notification.className = `notification ${type}`
    notification.textContent = message
    notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            border-radius: 4px;
            color: white;
            font-weight: 500;
            z-index: 3000;
            animation: slideIn 0.3s ease-out;
        `

    if (type === "error") {
      notification.style.backgroundColor = "#e74c3c"
    } else if (type === "success") {
      notification.style.backgroundColor = "#27ae60"
    }

    document.body.appendChild(notification)

    setTimeout(() => {
      notification.style.animation = "slideOut 0.3s ease-in"
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove()
        }
      }, 300)
    }, 3000)
  }
}

// Add CSS animations for notifications
const style = document.createElement("style")
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`
document.head.appendChild(style)

// Initialize the application
const app = new MyKitchenApp()
