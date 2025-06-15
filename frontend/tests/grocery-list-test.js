/**
 * Grocery List Frontend Test
 * Tests the grocery list functionality including adding, toggling, and deleting items
 */

// Mock fetch for testing
class MockFetch {
  constructor() {
    this.responses = new Map()
    this.requests = []
  }

  setResponse(url, response) {
    this.responses.set(url, response)
  }

  async fetch(url, options = {}) {
    this.requests.push({ url, options })

    const response = this.responses.get(url)
    if (!response) {
      throw new Error(`No mock response set for ${url}`)
    }

    return {
      ok: response.ok !== false,
      json: async () => response.data || response,
      status: response.status || 200,
    }
  }

  getRequests() {
    return this.requests
  }

  clearRequests() {
    this.requests = []
  }
}

// Test Suite for Grocery List Functionality
class GroceryListTest {
  constructor() {
    this.mockFetch = new MockFetch()
    this.originalFetch = global.fetch
    this.testResults = []

    console.log("=== Grocery List Frontend Tests ===")
  }

  setupMocks() {
    // Mock DOM elements
    global.document = {
      getElementById: (id) => {
        const mockElements = {
          "quick-item-name": { value: "", reset: () => {} },
          "quick-quantity": { value: "", reset: () => {} },
          "quick-add-form": { reset: () => {} },
          "grocery-list": { innerHTML: "" },
          "dashboard-grocery": { innerHTML: "" },
        }
        return mockElements[id] || { value: "", innerHTML: "", reset: () => {} }
      },
      createElement: () => ({ style: {}, textContent: "", className: "" }),
      head: { appendChild: () => {} },
      body: { appendChild: () => {} },
      querySelector: () => null,
      querySelectorAll: () => [],
    }

    // Mock fetch
    global.fetch = this.mockFetch.fetch.bind(this.mockFetch)

    // Mock console for notifications
    global.console = {
      log: () => {},
      error: () => {},
    }
  }

  teardownMocks() {
    global.fetch = this.originalFetch
  }

  async testAddGroceryItem() {
    console.log("\n--- Test: Add Grocery Item ---")

    // Setup mock response for successful item creation
    this.mockFetch.setResponse("../backend/controllers/GroceryController.php?action=create", {
      message: "Grocery item added successfully",
    })

    // Setup mock response for loading grocery list
    this.mockFetch.setResponse("../backend/controllers/GroceryController.php?action=getAll", [
      {
        id: 1,
        item_name: "Test Item",
        quantity: "2 lbs",
        is_completed: false,
      },
    ])

    // Create a simplified app instance for testing
    const app = {
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
            await this.loadGroceryList()
            return { success: true, message: data.message }
          } else {
            return { success: false, error: data.error }
          }
        } catch (error) {
          return { success: false, error: error.message }
        }
      },

      async loadGroceryList() {
        const response = await fetch("../backend/controllers/GroceryController.php?action=getAll")
        const items = await response.json()
        this.renderGroceryList(items)
        return items
      },

      renderGroceryList(items) {
        const container = document.getElementById("grocery-list")
        if (items.length === 0) {
          container.innerHTML = '<div class="grocery-empty">Your grocery list is empty</div>'
          return
        }

        container.innerHTML = items
          .map(
            (item) => `
          <div class="grocery-item ${item.is_completed ? "completed" : ""}">
            <input type="checkbox" ${item.is_completed ? "checked" : ""}>
            <span>${item.item_name}</span>
            ${item.quantity ? `<span>(${item.quantity})</span>` : ""}
          </div>
        `,
          )
          .join("")
      },
    }

    // Set up form values
    document.getElementById("quick-item-name").value = "Test Item"
    document.getElementById("quick-quantity").value = "2 lbs"

    // Test adding item
    const mockEvent = { preventDefault: () => {} }
    const result = await app.handleQuickAdd(mockEvent)

    // Assertions
    if (result.success) {
      console.log("✅ PASS: Grocery item added successfully")

      // Check if correct API call was made
      const requests = this.mockFetch.getRequests()
      const createRequest = requests.find((req) => req.url.includes("action=create"))

      if (createRequest) {
        console.log("✅ PASS: Correct API endpoint called")

        const requestBody = JSON.parse(createRequest.options.body)
        if (requestBody.item_name === "Test Item" && requestBody.quantity === "2 lbs") {
          console.log("✅ PASS: Correct data sent to API")
          console.log(`   - Item: ${requestBody.item_name}`)
          console.log(`   - Quantity: ${requestBody.quantity}`)
        } else {
          console.log("❌ FAIL: Incorrect data sent to API")
        }
      } else {
        console.log("❌ FAIL: API endpoint not called")
      }

      // Test list loading
      const items = await app.loadGroceryList()
      if (items.length > 0 && items[0].item_name === "Test Item") {
        console.log("✅ PASS: Grocery list loaded correctly after adding item")
      } else {
        console.log("❌ FAIL: Grocery list not loaded correctly")
      }
    } else {
      console.log("❌ FAIL: Failed to add grocery item")
      console.log(`   Error: ${result.error}`)
    }

    this.mockFetch.clearRequests()
  }

  async testToggleGroceryItem() {
    console.log("\n--- Test: Toggle Grocery Item ---")

    // Setup mock response for toggle
    this.mockFetch.setResponse("../backend/controllers/GroceryController.php?action=toggle&id=1", {
      message: "Grocery item status updated",
    })

    // Setup mock response for reloading list
    this.mockFetch.setResponse("../backend/controllers/GroceryController.php?action=getAll", [
      {
        id: 1,
        item_name: "Test Item",
        quantity: "2 lbs",
        is_completed: true, // Now completed
      },
    ])

    const app = {
      async toggleGroceryItem(id) {
        try {
          const response = await fetch(`../backend/controllers/GroceryController.php?action=toggle&id=${id}`, {
            method: "PUT",
          })

          const data = await response.json()

          if (response.ok) {
            await this.loadGroceryList()
            return { success: true, message: data.message }
          } else {
            return { success: false, error: data.error }
          }
        } catch (error) {
          return { success: false, error: error.message }
        }
      },

      async loadGroceryList() {
        const response = await fetch("../backend/controllers/GroceryController.php?action=getAll")
        const items = await response.json()
        return items
      },
    }

    // Test toggling item
    const result = await app.toggleGroceryItem(1)

    if (result.success) {
      console.log("✅ PASS: Grocery item toggled successfully")

      // Check API call
      const requests = this.mockFetch.getRequests()
      const toggleRequest = requests.find((req) => req.url.includes("action=toggle&id=1"))

      if (toggleRequest && toggleRequest.options.method === "PUT") {
        console.log("✅ PASS: Correct toggle API call made")
      } else {
        console.log("❌ FAIL: Incorrect toggle API call")
      }

      // Check if list was reloaded
      const reloadRequest = requests.find((req) => req.url.includes("action=getAll"))
      if (reloadRequest) {
        console.log("✅ PASS: Grocery list reloaded after toggle")
      } else {
        console.log("❌ FAIL: Grocery list not reloaded after toggle")
      }
    } else {
      console.log("❌ FAIL: Failed to toggle grocery item")
      console.log(`   Error: ${result.error}`)
    }

    this.mockFetch.clearRequests()
  }

  async testDeleteGroceryItem() {
    console.log("\n--- Test: Delete Grocery Item ---")

    // Setup mock response for delete
    this.mockFetch.setResponse("../backend/controllers/GroceryController.php?action=delete&id=1", {
      message: "Grocery item deleted successfully",
    })

    // Setup mock response for reloading empty list
    this.mockFetch.setResponse("../backend/controllers/GroceryController.php?action=getAll", [])

    const app = {
      async deleteGroceryItem(id) {
        try {
          const response = await fetch(`../backend/controllers/GroceryController.php?action=delete&id=${id}`, {
            method: "DELETE",
          })

          const data = await response.json()

          if (response.ok) {
            await this.loadGroceryList()
            return { success: true, message: data.message }
          } else {
            return { success: false, error: data.error }
          }
        } catch (error) {
          return { success: false, error: error.message }
        }
      },

      async loadGroceryList() {
        const response = await fetch("../backend/controllers/GroceryController.php?action=getAll")
        const items = await response.json()
        this.renderGroceryList(items)
        return items
      },

      renderGroceryList(items) {
        const container = document.getElementById("grocery-list")
        if (items.length === 0) {
          container.innerHTML = '<div class="grocery-empty">Your grocery list is empty</div>'
        }
      },
    }

    // Test deleting item
    const result = await app.deleteGroceryItem(1)

    if (result.success) {
      console.log("✅ PASS: Grocery item deleted successfully")

      // Check API call
      const requests = this.mockFetch.getRequests()
      const deleteRequest = requests.find((req) => req.url.includes("action=delete&id=1"))

      if (deleteRequest && deleteRequest.options.method === "DELETE") {
        console.log("✅ PASS: Correct delete API call made")
      } else {
        console.log("❌ FAIL: Incorrect delete API call")
      }

      // Check if list was reloaded
      const items = await app.loadGroceryList()
      if (items.length === 0) {
        console.log("✅ PASS: Grocery list empty after deletion")
      } else {
        console.log("❌ FAIL: Grocery list not updated after deletion")
      }
    } else {
      console.log("❌ FAIL: Failed to delete grocery item")
      console.log(`   Error: ${result.error}`)
    }

    this.mockFetch.clearRequests()
  }

  async runAllTests() {
    this.setupMocks()

    try {
      await this.testAddGroceryItem()
      await this.testToggleGroceryItem()
      await this.testDeleteGroceryItem()
    } catch (error) {
      console.log(`❌ Test suite error: ${error.message}`)
    } finally {
      this.teardownMocks()
    }

    console.log("\n=== All Grocery List Tests Completed ===")
  }
}

// Run the tests
const groceryTest = new GroceryListTest()
groceryTest.runAllTests()
