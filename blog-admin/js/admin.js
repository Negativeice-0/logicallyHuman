// Admin JavaScript - Unified functionality
class AdminAI {
  constructor() {
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.setupNavigationProgress();
    this.setupFormValidation();
  }

  setupEventListeners() {
    // Auto-submit forms on select change
    document.querySelectorAll("select[data-auto-submit]").forEach((select) => {
      select.addEventListener("change", function () {
        this.form.submit();
      });
    });

    // Confirm destructive actions
    document
      .querySelectorAll("a[data-confirm], button[data-confirm]")
      .forEach((el) => {
        el.addEventListener("click", function (e) {
          const message = this.getAttribute("data-confirm") || "Are you sure?";
          if (!confirm(message)) {
            e.preventDefault();
            e.stopPropagation();
          }
        });
      });

    // File preview
    document
      .querySelectorAll('input[type="file"][data-preview]')
      .forEach((input) => {
        input.addEventListener("change", this.previewImage.bind(this));
      });
  }

  setupNavigationProgress() {
    window.addEventListener("scroll", () => {
      const winHeight = window.innerHeight;
      const docHeight = document.documentElement.scrollHeight;
      const scrollTop = window.pageYOffset;
      const trackLength = docHeight - winHeight;

      if (trackLength > 0) {
        const progress = Math.floor((scrollTop / trackLength) * 100);
        document.documentElement.style.setProperty(
          "--nav-scroll-progress",
          progress + "%",
        );
      }

      // Add scrolled class to navbar
      const navbar = document.querySelector(".navbar");
      if (scrollTop > 100) {
        navbar.classList.add("scrolled");
      } else {
        navbar.classList.remove("scrolled");
      }
    });
  }

  setupFormValidation() {
    const forms = document.querySelectorAll("form[data-validate]");
    forms.forEach((form) => {
      form.addEventListener("submit", function (e) {
        let valid = true;

        // Required fields
        this.querySelectorAll("[required]").forEach((field) => {
          if (!field.value.trim()) {
            valid = false;
            this.highlightError(field, "This field is required");
          }
        });

        // File size validation
        const fileInputs = this.querySelectorAll('input[type="file"]');
        fileInputs.forEach((input) => {
          if (input.files.length > 0) {
            const maxSize =
              input.getAttribute("data-max-size") || 5 * 1024 * 1024; // Default 5MB
            if (input.files[0].size > maxSize) {
              valid = false;
              this.highlightError(
                input,
                `File must be less than ${maxSize / 1024 / 1024}MB`,
              );
            }
          }
        });

        if (!valid) {
          e.preventDefault();
          this.showNotification("Please fix the errors in the form", "error");
        }
      });
    });
  }

  previewImage(e) {
    const input = e.target;
    const previewId = input.getAttribute("data-preview");
    const preview = document.getElementById(previewId);

    if (input.files && input.files[0] && preview) {
      const reader = new FileReader();

      reader.onload = (e) => {
        if (preview.tagName === "IMG") {
          preview.src = e.target.result;
        } else {
          preview.innerHTML = `
                        <div style="text-align: center; padding: 1rem;">
                            <img src="${e.target.result}" style="max-width: 300px; max-height: 200px; border-radius: 8px;">
                            <p class="text-muted mt-1">${input.files[0].name} (${(input.files[0].size / 1024).toFixed(2)} KB)</p>
                        </div>
                    `;
        }
      };

      reader.readAsDataURL(input.files[0]);
    }
  }

  showNotification(message, type = "info") {
    // Remove existing notifications
    document.querySelectorAll(".admin-notification").forEach((n) => n.remove());

    const notification = document.createElement("div");
    notification.className = `admin-notification notification-${type}`;
    notification.innerHTML = `
            <div style="
                position: fixed;
                top: 100px;
                right: 20px;
                padding: 15px 20px;
                background: ${type === "error" ? "#ff0050" : type === "success" ? "#4CAF50" : type === "warning" ? "#FFC107" : "#2196F3"};
                color: white;
                border-radius: 8px;
                z-index: 9999;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideInRight 0.3s ease-out;
                max-width: 400px;
            ">
                <i class="fas ${type === "error" ? "fa-exclamation-circle" : type === "success" ? "fa-check-circle" : "fa-info-circle"}"></i>
                <span>${message}</span>
            </div>
        `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
      if (notification.parentNode) {
        notification.style.animation = "slideOutRight 0.3s ease-in";
        setTimeout(() => {
          if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
          }
        }, 300);
      }
    }, 5000);

    // Add close button functionality
    notification.addEventListener("click", () => {
      if (notification.parentNode) {
        notification.parentNode.removeChild(notification);
      }
    });
  }

  highlightError(element, message) {
    element.style.borderColor = "#ff0050";

    // Remove existing error
    const existingError = element.parentNode.querySelector(".field-error");
    if (existingError) existingError.remove();

    // Add error message
    const error = document.createElement("div");
    error.className = "field-error";
    error.style.cssText = `
            color: #ff0050;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        `;
    error.textContent = message;

    element.parentNode.appendChild(error);

    // Focus on first error
    if (!this.firstError) {
      this.firstError = element;
      element.focus();
    }
  }

  // Analytics functions
  async fetchAnalytics(endpoint, params = {}) {
    try {
      const response = await fetch(`ai/api/${endpoint}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(params),
      });

      return await response.json();
    } catch (error) {
      console.error("Analytics fetch error:", error);
      return null;
    }
  }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.adminAI = new AdminAI();

  // Add CSS animations
  if (!document.querySelector("#admin-animations")) {
    const style = document.createElement("style");
    style.id = "admin-animations";
    style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .fade-in {
                animation: fadeIn 0.5s ease-out;
            }
        `;
    document.head.appendChild(style);
  }
});

// Utility functions
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

function formatBytes(bytes, decimals = 2) {
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + " " + sizes[i];
}

// Export for module systems
if (typeof module !== "undefined" && module.exports) {
  module.exports = { AdminAI, debounce, formatBytes };
}
