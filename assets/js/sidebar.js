class SidebarManager {
  constructor() {
    this.sidebar = null;
    this.toggleBtn = null;
    this.mobileToggle = null;
    this.overlay = null;
    this.dropdownItems = [];
    this.isCollapsed = false;
    this.isMobile = window.innerWidth <= 768;

    this.init();
  }

  init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => this.setup());
    } else {
      this.setup();
    }
  }

  setup() {
    this.sidebar = document.querySelector(".sidebar");
    this.toggleBtn = document.querySelector(".sidebar-toggle");
    this.mobileToggle = document.querySelector(".mobile-toggle");
    this.expandIndicator = document.querySelector(".expand-indicator");
    this.overlay = document.querySelector(".sidebar-overlay");
    this.dropdownItems = document.querySelectorAll(".nav-item.dropdown");

    if (!this.sidebar) {
      return;
    }

    // Initialize mobile toggle visibility
    if (this.mobileToggle) {
      if (this.isMobile) {
        this.mobileToggle.classList.add("show");
      } else {
        this.mobileToggle.classList.remove("show");
      }
    }

    this.setupEventListeners();
    this.setupDropdowns();
    this.setupUserProfile();
    this.setupTooltips();
    this.handleResize();
    this.updateExpandIndicator();
  }

  setupEventListeners() {
    // Eliminar toggle desktop: solo usamos móvil
    // Quitar listener de this.toggleBtn si existe
    if (this.toggleBtn) {
      this.toggleBtn.remove();
      this.toggleBtn = null;
    }

    if (this.mobileToggle) {
      this.mobileToggle.addEventListener("click", () =>
        this.toggleMobileSidebar()
      );
    }

    if (this.overlay) {
      this.overlay.addEventListener("click", () => this.closeMobileSidebar());
    }

    // Window resize handler
    window.addEventListener("resize", () => this.handleResize());

    // ESC key to close mobile sidebar
    document.addEventListener("keydown", (e) => {
      if (
        e.key === "Escape" &&
        this.isMobile &&
        this.sidebar.classList.contains("show")
      ) {
        this.closeMobileSidebar();
      }
    });
  }

  setupDropdowns() {
    this.dropdownItems.forEach((item) => {
      const link = item.querySelector(".nav-link");
      const dropdown = item.querySelector(".nav-dropdown");

      if (link && dropdown) {
        link.addEventListener("click", (e) => {
          e.preventDefault();
          this.toggleDropdown(item);
        });
      }
    });
  }

  setupUserProfile() {
    const userProfile = document.querySelector(".user-profile");
    const userInfo = document.querySelector(".user-info");
    const userDropdown = document.querySelector(".user-dropdown");

    if (userProfile && userInfo && userDropdown) {
      userInfo.addEventListener("click", (e) => {
        e.preventDefault();
        this.toggleUserDropdown(userProfile, userDropdown);
      });

      // Close dropdown when clicking outside
      document.addEventListener("click", (e) => {
        if (!userProfile.contains(e.target)) {
          this.closeUserDropdown(userProfile, userDropdown);
        }
      });
    }
  }

  toggleUserDropdown(userProfile, userDropdown) {
    const isOpen = userProfile.classList.contains("open");

    if (isOpen) {
      this.closeUserDropdown(userProfile, userDropdown);
    } else {
      this.openUserDropdown(userProfile, userDropdown);
    }
  }

  openUserDropdown(userProfile, userDropdown) {
    userProfile.classList.add("open");
    userDropdown.classList.add("show");
  }

  closeUserDropdown(userProfile, userDropdown) {
    userProfile.classList.remove("open");
    userDropdown.classList.remove("show");
  }

  setupTooltips() {
    // Configurar tooltips personalizados para todos los estados
    this.initializeCustomTooltips();

    // Initialize Bootstrap tooltips for collapsed sidebar (fallback)
    if (typeof bootstrap !== "undefined" && bootstrap.Tooltip) {
      this.updateTooltips();
    }
  }

  initializeCustomTooltips() {
    const navLinks = document.querySelectorAll(".nav-link");

    navLinks.forEach((link) => {
      // Obtener el texto del nav-text para tooltips cuando está colapsado
      const navText = link.querySelector(".nav-text");
      const originalTitle = link.getAttribute("title");

      if (navText || originalTitle) {
        const tooltipText = originalTitle || navText.textContent.trim();

        // Almacenar tanto el título original como el texto del nav
        link.setAttribute(
          "data-tooltip-text",
          navText ? navText.textContent.trim() : tooltipText
        );
        link.setAttribute("data-original-title", originalTitle || tooltipText);

        // Remover title nativo para evitar conflictos
        link.addEventListener("mouseenter", () => {
          link.removeAttribute("title");
        });

        // No restaurar title al salir, usamos CSS para los tooltips
        link.addEventListener("mouseleave", () => {
          // Los tooltips CSS se manejan automáticamente
        });
      }
    });
  }

  updateTooltipsOnToggle() {
    // Actualizar todos los tooltips cuando cambia el estado del sidebar
    const navLinks = document.querySelectorAll(".nav-link");

    navLinks.forEach((link) => {
      const navText = link.querySelector(".nav-text");
      const originalTitle = link.getAttribute("data-original-title");

      if (navText && originalTitle) {
        // Asegurar que el atributo data-tooltip-text esté actualizado
        link.setAttribute("data-tooltip-text", navText.textContent.trim());
      }
    });
  }

  updateTooltips() {
    const existingTooltips = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    existingTooltips.forEach((el) => {
      const tooltip = bootstrap.Tooltip.getInstance(el);
      if (tooltip) tooltip.dispose();
    });

    // Add tooltips when collapsed
    if (this.isCollapsed && !this.isMobile) {
      const navLinks = this.sidebar.querySelectorAll(
        ".nav-link:not(.dropdown .nav-link)"
      );
      navLinks.forEach((link) => {
        const text = link.querySelector(".nav-text");
        if (text) {
          link.setAttribute("data-bs-toggle", "tooltip");
          link.setAttribute("data-bs-placement", "right");
          link.setAttribute("title", text.textContent.trim());
          new bootstrap.Tooltip(link);
        }
      });
    }
  }

  updateExpandIndicator() {
    if (!this.expandIndicator) return;

    if (!this.isMobile && this.isCollapsed) {
      this.expandIndicator.style.display = "flex";
    } else {
      this.expandIndicator.style.display = "none";
    }
  }

  // Desactivar completamente colapso en desktop
  toggleSidebar() {
    // No-op en desktop
    return;
  }

  // Mantener móvil igual
  toggleMobileSidebar() {
    const isOpen = this.sidebar.classList.contains("show");

    if (isOpen) {
      this.closeMobileSidebar();
    } else {
      this.openMobileSidebar();
    }
  }

  openMobileSidebar() {
    this.sidebar.classList.add("show");
    if (this.overlay) {
      this.overlay.classList.add("show");
    }
    if (this.mobileToggle) {
      this.mobileToggle.classList.remove("show");
    }

    document.body.style.overflow = "hidden";

    this.dispatchEvent("sidebarOpen");
  }

  closeMobileSidebar() {
    this.sidebar.classList.remove("show");
    if (this.overlay) {
      this.overlay.classList.remove("show");
    }
    if (this.mobileToggle) {
      this.mobileToggle.classList.add("show");
    }

    document.body.style.overflow = "";

    this.dispatchEvent("sidebarClose");
  }

  toggleDropdown(item) {
    const isOpen = item.classList.contains("open");
    const dropdown = item.querySelector(".nav-dropdown");

    if (this.isCollapsed && !this.isMobile) {
      return;
    }

    this.dropdownItems.forEach((otherItem) => {
      if (otherItem !== item && otherItem.classList.contains("open")) {
        this.closeDropdown(otherItem);
      }
    });

    if (isOpen) {
      this.closeDropdown(item);
    } else {
      this.openDropdown(item);
    }
  }

  openDropdown(item) {
    const dropdown = item.querySelector(".nav-dropdown");
    item.classList.add("open");

    if (dropdown) {
      dropdown.classList.add("show");
    }

    this.dispatchEvent("dropdownOpen", { item });
  }

  closeDropdown(item) {
    const dropdown = item.querySelector(".nav-dropdown");
    item.classList.remove("open");

    if (dropdown) {
      dropdown.classList.remove("show");
    }

    this.dispatchEvent("dropdownClose", { item });
  }

  closeAllDropdowns() {
    this.dropdownItems.forEach((item) => {
      this.closeDropdown(item);
    });
  }

  handleResize() {
    this.isMobile = window.innerWidth <= 768;

    if (!this.isMobile) {
      // Asegurar estado expandido en desktop
      this.isCollapsed = false;
      this.sidebar.classList.remove("collapsed");
      if (this.overlay) this.overlay.classList.remove("show");
      document.body.style.overflow = "";
    }

    if (this.mobileToggle) {
      if (this.isMobile) this.mobileToggle.classList.add("show");
      else this.mobileToggle.classList.remove("show");
    }
  }

  collapse() {
    if (!this.isMobile && !this.isCollapsed) {
      this.toggleSidebar();
    }
  }

  expand() {
    if (!this.isMobile && this.isCollapsed) {
      this.toggleSidebar();
    }
  }

  expandSidebar() {
    if (!this.isMobile && this.isCollapsed) {
      this.toggleSidebar();
    }
  }

  setActiveItem(selector) {
    const allLinks = this.sidebar.querySelectorAll(".nav-link");
    allLinks.forEach((link) => link.classList.remove("active"));

    const activeLink = this.sidebar.querySelector(selector);
    if (activeLink) {
      activeLink.classList.add("active");

      const parentDropdown = activeLink.closest(".nav-dropdown");
      if (parentDropdown) {
        const parentItem = parentDropdown.closest(".nav-item");
        if (parentItem) {
          this.openDropdown(parentItem);
        }
      }
    }
  }

  dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent(`sidebar:${eventName}`, {
      detail: { ...detail, sidebar: this },
    });
    document.dispatchEvent(event);
  }
}

let sidebarManager;

document.addEventListener("DOMContentLoaded", () => {
  sidebarManager = new SidebarManager();
});

window.SidebarManager = SidebarManager;
window.sidebarManager = sidebarManager;

document.addEventListener("DOMContentLoaded", () => {
  setTimeout(() => {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll(".sidebar .nav-link[href]");

    navLinks.forEach((link) => {
      const href = link.getAttribute("href");
      if (href && currentPath.includes(href)) {
        link.classList.add("active");

        const parentDropdown = link.closest(".nav-dropdown");
        if (parentDropdown && sidebarManager) {
          const parentItem = parentDropdown.closest(".nav-item");
          if (parentItem) {
            sidebarManager.openDropdown(parentItem);
          }
        }
      }
    });
  }, 100);
});
