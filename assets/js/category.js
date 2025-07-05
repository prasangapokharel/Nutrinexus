class CategoryGrid {
  constructor(options = {}) {
    this.options = {
      categories: options.categories || [
        {
          title: "25% OFF",
          subtitle: "",
          background: "linear-gradient(135deg, #E91E63, #AD1457)",
          icon: "🏷️",
          href: "#sale",
        },
        {
          title: "DataLock",
          subtitle: "",
          background: "linear-gradient(135deg, #9C27B0, #7B1FA2)",
          icon: "👥",
          href: "#datalock",
        },
        {
          title: "Buy Any 3",
          subtitle: "CHOICE",
          background: "linear-gradient(135deg, #FF9800, #F57C00)",
          icon: "🛒",
          href: "#buy3",
        },
        {
          title: "Free Delivery",
          subtitle: "",
          background: "linear-gradient(135deg, #4CAF50, #388E3C)",
          icon: "🚚",
          href: "#delivery",
        },
        {
          title: "Beauty",
          subtitle: "",
          background: "linear-gradient(135deg, #FF5722, #D84315)",
          imageUrl: "https://via.placeholder.com/100x80/FF5722/FFFFFF?text=Beauty",
          href: "#beauty",
        },
        {
          title: "Early Bird Deals",
          subtitle: "",
          background: "linear-gradient(135deg, #673AB7, #512DA8)",
          icon: "🐦",
          href: "#earlybird",
        },
        {
          title: "Play & Win",
          subtitle: "",
          background: "linear-gradient(135deg, #2196F3, #1976D2)",
          icon: "🎮",
          href: "#playwin",
        },
      ],
      container: options.container || null,
      columns: options.columns || { mobile: 3, tablet: 3, desktop: 4 },
      gap: options.gap || 12,
      itemHeight: options.itemHeight || 80,
      borderRadius: options.borderRadius || 12,
      ...options,
    }

    this.element = null
    this.cachedCategories = this.getCachedCategories()

    this.init()
  }

  init() {
    this.createElement()
    this.setupStyles()
    this.addContent()
    this.attachEvents()
    this.insertGrid()
  }

  createElement() {
    this.element = document.createElement("div")
    this.element.className = "category-grid"
  }

  setupStyles() {
    const { gap, columns } = this.options

    Object.assign(this.element.style, {
      display: "grid",
      gap: `${gap}px`,
      padding: "20px",
      gridTemplateColumns: `repeat(${this.getColumnCount()}, 1fr)`,
      maxWidth: "100%",
      margin: "0 auto",
    })

    // Responsive grid
    this.addResponsiveStyles()
  }

  getColumnCount() {
    const width = window.innerWidth
    if (width <= 768) return this.options.columns.mobile
    if (width <= 1024) return this.options.columns.tablet
    return this.options.columns.desktop
  }

  addResponsiveStyles() {
    const { columns, gap } = this.options

    // Create responsive CSS
    const style = document.createElement("style")
    style.textContent = `
      .category-grid {
        display: grid;
        gap: ${gap}px;
        grid-template-columns: repeat(${columns.desktop}, 1fr);
      }
      
      @media (max-width: 1024px) {
        .category-grid {
          grid-template-columns: repeat(${columns.tablet}, 1fr);
        }
      }
      
      @media (max-width: 768px) {
        .category-grid {
          grid-template-columns: repeat(${columns.mobile}, 1fr);
          padding: 15px;
          gap: ${gap - 2}px;
        }
      }
      
      .category-item {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
      }
      
      .category-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
      }
      
      .category-item:active {
        transform: translateY(0);
      }
    `

    if (!document.querySelector("#category-grid-styles")) {
      style.id = "category-grid-styles"
      document.head.appendChild(style)
    }
  }

  insertGrid() {
    const container = this.options.container
    if (container) {
      if (typeof container === "string") {
        const targetElement = document.querySelector(container)
        if (targetElement) {
          targetElement.appendChild(this.element)
        }
      } else {
        container.appendChild(this.element)
      }
    } else {
      // Insert after banner or at top of body
      const banner = document.querySelector(".clean-banner-slider")
      if (banner) {
        banner.insertAdjacentElement("afterend", this.element)
      } else {
        document.body.appendChild(this.element)
      }
    }
  }

  getCachedCategories() {
    const cookies = document.cookie.split("; ")
    const categoriesCookie = cookies.find((row) => row.startsWith("categories="))
    if (categoriesCookie) {
      try {
        return JSON.parse(decodeURIComponent(categoriesCookie.split("=")[1]))
      } catch (e) {
        return null
      }
    }
    return null
  }

  setCachedCategories(categories) {
    const expires = new Date()
    expires.setTime(expires.getTime() + 24 * 60 * 60 * 1000) // 24 hours
    const categoriesData = JSON.stringify(categories)
    document.cookie = `categories=${encodeURIComponent(categoriesData)}; expires=${expires.toUTCString()}; path=/`
  }

  addContent() {
    const categories = this.cachedCategories || this.options.categories

    categories.forEach((category, index) => {
      const categoryItem = this.createCategoryItem(category, index)
      this.element.appendChild(categoryItem)
    })

    // Cache categories if not already cached
    if (!this.cachedCategories) {
      this.setCachedCategories(categories)
    }
  }

  createCategoryItem(category, index) {
    const { itemHeight, borderRadius } = this.options

    const item = document.createElement("div")
    item.className = "category-item"

    Object.assign(item.style, {
      height: `${itemHeight}px`,
      background: category.background || "#f5f5f5",
      borderRadius: `${borderRadius}px`,
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      justifyContent: "center",
      cursor: "pointer",
      position: "relative",
      overflow: "hidden",
      boxShadow: "0 2px 8px rgba(0, 0, 0, 0.1)",
      textAlign: "center",
      padding: "8px",
      boxSizing: "border-box",
    })

    // Add background image if provided
    if (category.imageUrl) {
      const bgImage = document.createElement("div")
      Object.assign(bgImage.style, {
        position: "absolute",
        top: "0",
        left: "0",
        right: "0",
        bottom: "0",
        backgroundImage: `url(${category.imageUrl})`,
        backgroundSize: "cover",
        backgroundPosition: "center",
        opacity: "0.8",
      })
      item.appendChild(bgImage)
    }

    // Content container
    const content = document.createElement("div")
    Object.assign(content.style, {
      position: "relative",
      zIndex: "3",
      color: "white",
      textShadow: "1px 1px 2px rgba(0, 0, 0, 0.5)",
    })

    // Icon
    if (category.icon) {
      const icon = document.createElement("div")
      icon.textContent = category.icon
      Object.assign(icon.style, {
        fontSize: "24px",
        marginBottom: "4px",
      })
      content.appendChild(icon)
    }

    // Title
    const title = document.createElement("div")
    title.textContent = category.title
    Object.assign(title.style, {
      fontSize: "12px",
      fontWeight: "600",
      lineHeight: "1.2",
      fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
    })
    content.appendChild(title)

    // Subtitle
    if (category.subtitle) {
      const subtitle = document.createElement("div")
      subtitle.textContent = category.subtitle
      Object.assign(subtitle.style, {
        fontSize: "10px",
        fontWeight: "500",
        marginTop: "2px",
        opacity: "0.9",
      })
      content.appendChild(subtitle)
    }

    item.appendChild(content)

    // Click handler
    if (category.href) {
      item.addEventListener("click", () => {
        this.handleCategoryClick(category, index)
      })
    }

    return item
  }

  handleCategoryClick(category, index) {
    console.log("Category clicked:", category.title)

    // Dispatch custom event
    window.dispatchEvent(
      new CustomEvent("categoryClick", {
        detail: { category, index },
      }),
    )

    // Navigate to href
    if (category.href) {
      if (category.href.startsWith("http")) {
        window.open(category.href, "_blank")
      } else {
        window.location.href = category.href
      }
    }
  }

  attachEvents() {
    // Responsive resize handler
    this.resizeHandler = () => {
      this.element.style.gridTemplateColumns = `repeat(${this.getColumnCount()}, 1fr)`
    }

    window.addEventListener("resize", this.resizeHandler)
  }

  updateCategories(newCategories) {
    this.options.categories = newCategories
    this.setCachedCategories(newCategories)

    // Clear and rebuild
    this.element.innerHTML = ""
    this.addContent()
  }

  addCategory(category) {
    const categories = [...this.getCategories(), category]
    this.updateCategories(categories)
  }

  removeCategory(index) {
    const categories = this.getCategories().filter((_, i) => i !== index)
    this.updateCategories(categories)
  }

  getCategories() {
    return this.cachedCategories || this.options.categories
  }

  updateLayout(columns) {
    this.options.columns = { ...this.options.columns, ...columns }
    this.element.style.gridTemplateColumns = `repeat(${this.getColumnCount()}, 1fr)`
  }

  destroy() {
    if (this.resizeHandler) {
      window.removeEventListener("resize", this.resizeHandler)
    }

    if (this.element && this.element.parentNode) {
      this.element.remove()
    }

    // Remove styles
    const styles = document.querySelector("#category-grid-styles")
    if (styles) {
      styles.remove()
    }
  }
}

// Initialize the category grid
const categoryGrid = new CategoryGrid({
  categories: [
    {
      title: "25%",
      subtitle: "OFF",
      background: "linear-gradient(135deg, #E91E63, #AD1457)",
      icon: "🏷️",
      href: "#sale",
    },
    {
      title: "DataLock",
      subtitle: "",
      background: "linear-gradient(135deg, #9C27B0, #7B1FA2)",
      icon: "👥",
      href: "#datalock",
    },
    {
      title: "Buy",
      subtitle: "Any 3",
      background: "linear-gradient(135deg, #FF9800, #F57C00)",
      icon: "🛒",
      href: "#buy3",
    },
    {
      title: "Free",
      subtitle: "Delivery",
      background: "linear-gradient(135deg, #4CAF50, #388E3C)",
      icon: "🚚",
      href: "#delivery",
    },
    {
      title: "Beauty",
      subtitle: "",
      background: "linear-gradient(135deg, #FF5722, #D84315)",
      imageUrl: "https://via.placeholder.com/100x80/FF5722/FFFFFF?text=👩",
      href: "#beauty",
    },
    {
      title: "Early Bird",
      subtitle: "Deals",
      background: "linear-gradient(135deg, #673AB7, #512DA8)",
      icon: "🐦",
      href: "#earlybird",
    },
    {
      title: "Play & Win",
      subtitle: "",
      background: "linear-gradient(135deg, #2196F3, #1976D2)",
      icon: "🎮",
      href: "#playwin",
    },
  ],
  columns: { mobile: 2, tablet: 3, desktop: 4 },
  gap: 12,
  itemHeight: 80,
})

if (typeof module !== "undefined" && module.exports) {
  module.exports = CategoryGrid
}
