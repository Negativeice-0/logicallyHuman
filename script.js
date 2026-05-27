/**
 * LOGICAL HUMAN - Complete JavaScript Functionality
 * Handles SPA navigation, form validation, dynamic content, and comments
 */

// ===== TIER 1.2: DYNAMIC CONTENT RENDERER =====
const featuredPosts = [
  {
    title: "The Rise of Human-AI Collaboration",
    excerpt:
      "Exploring how algorithms and human creativity merge to define future design and art.",
    imageUrl: "./images/i1.jpg",
    date: "2024-10-15",
  },
  {
    title: "Designing for Mobile-First PWA",
    excerpt:
      "Deep dive into philosophy and technical requirements for building fast, reliable mobile-first PWAs.",
    imageUrl: "./images/i4.jpeg",
    date: "2024-09-28",
  },
  {
    title: "Vanilla JS: Why it Still Matters",
    excerpt:
      "Forget frameworks. Explore the power, performance, and simplicity of plain JavaScript.",
    imageUrl: "./images/i3.png",
    date: "2024-09-10",
  },
];

function loadFeaturedPosts() {
  const container = document.getElementById("featured-posts-container");
  if (!container) return;

  if (featuredPosts.length === 0) {
    container.innerHTML =
      '<p class="no-posts-message">No featured posts at this time.</p>';
    return;
  }

  container.innerHTML = featuredPosts
    .map(
      (post) => `
    <div class="featured-post glass-card">
      ${post.imageUrl ? `<img src="${post.imageUrl}" alt="${post.title}" class="post-image" />` : ""}
      <div class="post-content">
        <h3>${escapeHtml(post.title)}</h3>
        <p class="excerpt">${escapeHtml(post.excerpt)}</p>
        <small>Published: ${post.date}</small>
      </div>
    </div>
  `,
    )
    .join("");
}

// ===== TIER 1.1: FORM VALIDATION & USER FEEDBACK =====
function displayError(inputId, message) {
  const inputElement = document.getElementById(inputId);
  if (!inputElement) return;

  let errorSpan = document.getElementById(inputId + "-error");
  if (!errorSpan) {
    errorSpan = document.createElement("span");
    errorSpan.id = inputId + "-error";
    errorSpan.className = "error-message";
    inputElement.parentNode.appendChild(errorSpan);
  }

  errorSpan.textContent = message;
  errorSpan.style.display = message ? "block" : "none";
  inputElement.style.borderColor = message ? "#ff0050" : "#ccc";
}

function validateField(fieldId, validationFn) {
  const element = document.getElementById(fieldId);
  if (!element) return true;

  return validationFn(element.value, element);
}

function validateRegistrationForm() {
  let isValid = true;

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (
    !validateField("email", (value) => {
      if (!value) {
        displayError("email", "Email is required.");
        return false;
      }
      if (!emailRegex.test(value)) {
        displayError("email", "Invalid email format.");
        return false;
      }
      displayError("email", "");
      return true;
    })
  )
    isValid = false;

  // Username validation
  if (
    !validateField("username", (value) => {
      if (!value) {
        displayError("username", "Username is required.");
        return false;
      }
      if (value.length < 3) {
        displayError("username", "Username must be at least 3 characters.");
        return false;
      }
      displayError("username", "");
      return true;
    })
  )
    isValid = false;

  // Password validation
  if (
    !validateField("password", (value) => {
      if (!value) {
        displayError("password", "Password is required.");
        return false;
      }
      if (value.length < 8) {
        displayError("password", "Password must be at least 8 characters.");
        return false;
      }
      displayError("password", "");
      return true;
    })
  )
    isValid = false;

  // Confirm password
  const password = document.getElementById("password")?.value || "";
  const confirmPassword =
    document.getElementById("confirm_password")?.value || "";
  if (password && confirmPassword && password !== confirmPassword) {
    displayError("confirm_password", "Passwords do not match.");
    isValid = false;
  } else {
    displayError("confirm_password", "");
  }

  updateSubmitButton();
  return isValid;
}

function validateLoginForm() {
  let isValid = true;

  if (
    !validateField("email", (value) => {
      if (!value) {
        displayError("email", "Email is required.");
        return false;
      }
      displayError("email", "");
      return true;
    })
  )
    isValid = false;

  if (
    !validateField("password", (value) => {
      if (!value) {
        displayError("password", "Password is required.");
        return false;
      }
      displayError("password", "");
      return true;
    })
  )
    isValid = false;

  updateLoginSubmitButton();
  return isValid;
}

function updateSubmitButton() {
  const submitBtn = document.getElementById("submit-btn");
  if (!submitBtn) return;

  const hasErrors =
    document.querySelectorAll('.error-message[style*="display: block"]')
      .length > 0;
  const requiredFields = ["username", "email", "password", "confirm_password"];
  const allFilled = requiredFields.every((id) => {
    const element = document.getElementById(id);
    return element && element.value.trim() !== "";
  });

  submitBtn.disabled = hasErrors || !allFilled;
}

function updateLoginSubmitButton() {
  const submitBtn = document.querySelector(
    'input[type="submit"][value="Login"]',
  );
  if (!submitBtn) return;

  const hasErrors =
    document.querySelectorAll('.error-message[style*="display: block"]')
      .length > 0;
  const email = document.getElementById("email")?.value.trim() || "";
  const password = document.getElementById("password")?.value.trim() || "";

  submitBtn.disabled = hasErrors || !email || !password;
}

// ===== TIER 2.1: SINGLE PAGE APPLICATION NAVIGATION =====
function navigateSPA(path) {
  const targetId = path.substring(1);
  const targetElement = document.getElementById(targetId);

  if (targetElement) {
    // Show loading indicator
    const loadingOverlay = showLoadingIndicator();

    setTimeout(() => {
      // Hide loading indicator
      hideLoadingIndicator(loadingOverlay);

      // Update URL
      window.history.pushState({ path: targetId }, "", `#${targetId}`);

      // Scroll to section
      targetElement.scrollIntoView({ behavior: "smooth" });

      // Update active nav link
      updateActiveNavLink(targetId);
    }, 300);
  }
}

function updateActiveNavLink(activeId) {
  document.querySelectorAll(".nav-links a").forEach((link) => {
    link.classList.remove("active");
    if (link.getAttribute("href") === `#${activeId}`) {
      link.classList.add("active");
    }
  });
}

function showLoadingIndicator() {
  const loadingOverlay = document.createElement("div");
  loadingOverlay.className = "loading-overlay";
  loadingOverlay.innerHTML = '<div class="spinner"></div>';
  document.body.appendChild(loadingOverlay);
  return loadingOverlay;
}

function hideLoadingIndicator(overlay) {
  if (overlay) {
    overlay.remove();
  }
}

// ===== TIER 2.2: COMMENT SYSTEM FRONTEND =====
const COMMENT_STORAGE_KEY = "logical_human_comments";

function getComments() {
  const commentsJson = localStorage.getItem(COMMENT_STORAGE_KEY);
  return commentsJson ? JSON.parse(commentsJson) : [];
}

function saveComments(comments) {
  localStorage.setItem(COMMENT_STORAGE_KEY, JSON.stringify(comments));
}

function renderComments() {
  const commentsList = document.getElementById("comments-list");
  if (!commentsList) return;

  const comments = getComments();
  commentsList.innerHTML = "";

  if (comments.length === 0) {
    commentsList.innerHTML =
      '<p style="color: #aaa; text-align: center;">Be the first to leave a comment!</p>';
    return;
  }

  comments
    .slice()
    .reverse()
    .forEach((comment) => {
      const commentDiv = document.createElement("div");
      commentDiv.className = "comment-entry";
      commentDiv.innerHTML = `
      <strong>${escapeHtml(comment.name)}</strong>
      <p>${escapeHtml(comment.text)}</p>
      <small>${comment.date}</small>
    `;
      commentsList.appendChild(commentDiv);
    });
}

function handleCommentSubmission(event) {
  event.preventDefault();

  const nameInput = document.getElementById("comment-name");
  const textInput = document.getElementById("comment-text");

  const name = nameInput.value.trim();
  const text = textInput.value.trim();

  if (name && text) {
    const newComment = {
      name: name,
      text: text,
      date:
        new Date().toLocaleDateString() + " " + new Date().toLocaleTimeString(),
    };

    const comments = getComments();
    comments.push(newComment);
    saveComments(comments);
    renderComments();

    // Reset form
    nameInput.value = "";
    textInput.value = "";
  }
}

// ===== UTILITY FUNCTIONS =====
function escapeHtml(unsafe) {
  return unsafe
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function handleNavigationClick(event) {
  const path = event.target.getAttribute("href");
  if (path && path.startsWith("#")) {
    event.preventDefault();
    navigateSPA(path);
  }
}

// ===== INITIALIZATION =====
document.addEventListener("DOMContentLoaded", () => {
  // Load featured posts on index.php
  if (
    document.getElementById("featured-posts-container") &&
    !document.querySelector(".featured-post")
  ) {
    // Only if not already loaded by PHP
    loadFeaturedPosts();
  }

  // Set up form validation
  const registrationForm = document.querySelector('form[name="register"]');
  if (registrationForm) {
    ["username", "email", "password", "confirm_password"].forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.addEventListener("input", validateRegistrationForm);
        element.addEventListener("blur", validateRegistrationForm);
      }
    });

    // Initial validation
    validateRegistrationForm();
  }

  const loginForm = document.querySelector('form[action*="login"]');
  if (loginForm) {
    ["email", "password"].forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.addEventListener("input", validateLoginForm);
        element.addEventListener("blur", validateLoginForm);
      }
    });

    // Initial validation
    validateLoginForm();
  }

  // Set up comments
  if (document.getElementById("comments-list")) {
    renderComments();
    const commentForm = document.getElementById("comment-form");
    if (commentForm) {
      commentForm.addEventListener("submit", handleCommentSubmission);
    }
  }

  // Set up SPA navigation
  document.querySelectorAll(".nav-links a").forEach((link) => {
    link.addEventListener("click", handleNavigationClick);
  });

  // Handle browser back/forward buttons
  window.addEventListener("popstate", (event) => {
    const path = window.location.hash.substring(1) || "home";
    const targetElement = document.getElementById(path);
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: "smooth" });
      updateActiveNavLink(path);
    }
  });

  // Set initial active nav link based on URL hash
  const initialHash = window.location.hash.substring(1) || "home";
  updateActiveNavLink(initialHash);

  // Register service worker for PWA
  if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
      navigator.serviceWorker.register("/sw.js").then(
        (registration) => {
          console.log(
            "ServiceWorker registration successful:",
            registration.scope,
          );
        },
        (err) => {
          console.log("ServiceWorker registration failed:", err);
        },
      );
    });
  }
});

// ===== ENHANCED NAVBAR SCROLL EFFECTS =====
function initNavbarEffects() {
  const navbar = document.querySelector(".navbar");
  const navLinks = document.querySelectorAll(".nav-links a");
  const firstSection =
    document.querySelector("main, .hero, #home") ||
    document.querySelector("section");

  // Add progress bar to navbar
  const progressWrap = document.createElement("div");
  progressWrap.className = "nav-progress-wrap";
  progressWrap.innerHTML = '<div class="nav-progress"></div>';
  document.querySelector(".nav-container").appendChild(progressWrap);

  function onScroll() {
    const scrolledY = window.scrollY || window.pageYOffset;

    // Toggle compact navbar
    if (firstSection) {
      const firstBottom =
        firstSection.getBoundingClientRect().bottom + window.scrollY;
      navbar.classList.toggle("scrolled", scrolledY > firstBottom - 20);
    } else {
      navbar.classList.toggle("scrolled", scrolledY > 40);
    }

    // Update nav progress
    const docHeight = Math.max(
      document.documentElement.scrollHeight,
      document.documentElement.offsetHeight,
    );
    const winHeight = window.innerHeight;
    const maxScroll = docHeight - winHeight;
    const pct = maxScroll > 0 ? Math.round((scrolledY / maxScroll) * 100) : 0;
    document.documentElement.style.setProperty(
      "--nav-scroll-progress",
      pct + "%",
    );

    // Active link highlighting
    navLinks.forEach((a) => {
      const targetId = a.getAttribute("href") || "";
      if (targetId.startsWith("#")) {
        const el = document.querySelector(targetId);
        if (el) {
          const rect = el.getBoundingClientRect();
          const inView = rect.top <= 100 && rect.bottom >= 100;
          a.classList.toggle("active", inView);
        }
      }
    });
  }

  window.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", onScroll);
  document.addEventListener("DOMContentLoaded", onScroll);
}

/**
 * AI FEATURE: Smart Content Assistant
 * Pre-trained model for auto-generating content suggestions
 */
class ContentAssistant {
  constructor() {
    this.model = this.initializeModel();
    this.trainingData = this.getTrainingData();
  }

  initializeModel() {
    // Simple Markov chain implementation for text generation
    return {
      chains: {},
      starters: [],
    };
  }

  getTrainingData() {
    return [
      "The future of technology lies in human-AI collaboration",
      "Modern web development requires understanding both frontend and backend",
      "Machine learning is transforming how we interact with digital content",
      "Progressive Web Apps provide native app experience in browsers",
      "User experience design focuses on creating meaningful interactions",
      "Cloud computing enables scalable and flexible applications",
      "Cybersecurity is essential in our connected digital world",
      "Data analytics helps businesses make informed decisions",
      "Artificial intelligence enhances human capabilities",
      "Mobile-first design ensures accessibility across all devices",
    ];
  }

  trainModel() {
    this.trainingData.forEach((text) => {
      const words = text.toLowerCase().split(" ");
      this.model.starters.push(words[0]);

      for (let i = 0; i < words.length - 1; i++) {
        const currentWord = words[i];
        const nextWord = words[i + 1];

        if (!this.model.chains[currentWord]) {
          this.model.chains[currentWord] = [];
        }
        this.model.chains[currentWord].push(nextWord);
      }
    });
  }

  generateContent(type = "title", seedWord = "") {
    this.trainModel();

    let output = "";
    let currentWord =
      seedWord ||
      this.model.starters[
        Math.floor(Math.random() * this.model.starters.length)
      ];

    output += currentWord;

    const maxLength = type === "title" ? 4 : 8;

    for (let i = 0; i < maxLength; i++) {
      if (this.model.chains[currentWord]) {
        const nextWords = this.model.chains[currentWord];
        currentWord = nextWords[Math.floor(Math.random() * nextWords.length)];
        output += " " + currentWord;
      } else {
        break;
      }
    }

    return this.capitalizeFirstLetter(output);
  }

  capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }

  // AI-powered sentiment analysis for comments
  analyzeSentiment(text) {
    const positiveWords = [
      "good",
      "great",
      "excellent",
      "awesome",
      "amazing",
      "love",
      "like",
      "nice",
      "wonderful",
      "fantastic",
    ];
    const negativeWords = [
      "bad",
      "terrible",
      "awful",
      "hate",
      "dislike",
      "horrible",
      "worst",
      "boring",
      "stupid",
    ];

    const words = text.toLowerCase().split(" ");
    let score = 0;

    words.forEach((word) => {
      if (positiveWords.includes(word)) score++;
      if (negativeWords.includes(word)) score--;
    });

    if (score > 0) return { sentiment: "positive", score };
    if (score < 0) return { sentiment: "negative", score };
    return { sentiment: "neutral", score };
  }
}

// Initialize AI Assistant
const contentAI = new ContentAssistant();

// AI Feature: Auto-content generation
function setupAIFeatures() {
  // Add AI buttons to post creation form
  const postForm = document.querySelector('form[action*="posting"]');
  if (postForm) {
    const titleField = document.getElementById("title");
    const contentField = document.getElementById("content");

    if (titleField) {
      const aiTitleBtn = document.createElement("button");
      aiTitleBtn.type = "button";
      aiTitleBtn.textContent = "🎯 AI Suggest Title";
      aiTitleBtn.style.margin = "5px";
      aiTitleBtn.style.padding = "5px 10px";
      aiTitleBtn.style.background = "var(--netflix-red)";
      aiTitleBtn.style.color = "white";
      aiTitleBtn.style.border = "none";
      aiTitleBtn.style.borderRadius = "3px";
      aiTitleBtn.style.cursor = "pointer";

      aiTitleBtn.addEventListener("click", () => {
        titleField.value = contentAI.generateContent("title");
      });

      titleField.parentNode.appendChild(aiTitleBtn);
    }

    if (contentField) {
      const aiContentBtn = document.createElement("button");
      aiContentBtn.type = "button";
      aiContentBtn.textContent = "🤖 AI Suggest Content";
      aiContentBtn.style.margin = "5px";
      aiContentBtn.style.padding = "5px 10px";
      aiContentBtn.style.background = "var(--netflix-red)";
      aiContentBtn.style.color = "white";
      aiContentBtn.style.border = "none";
      aiContentBtn.style.borderRadius = "3px";
      aiContentBtn.style.cursor = "pointer";

      aiContentBtn.addEventListener("click", () => {
        contentField.value = contentAI.generateContent("content");
      });

      contentField.parentNode.appendChild(aiContentBtn);
    }
  }

  // Add sentiment analysis to comments
  const commentForms = document.querySelectorAll("#comment-form");
  commentForms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const commentText = document.getElementById("comment-text")?.value;
      if (commentText) {
        const sentiment = contentAI.analyzeSentiment(commentText);
        console.log(
          `Comment sentiment: ${sentiment.sentiment} (score: ${sentiment.score})`,
        );

        // You could use this to flag negative comments for moderation
        if (sentiment.sentiment === "negative") {
          // Optional: Add visual indicator
          const indicator = document.createElement("div");
          indicator.textContent = `⚠️ This comment appears negative`;
          indicator.style.color = "orange";
          indicator.style.fontSize = "0.8em";
          form.appendChild(indicator);
        }
      }
    });
  });
}

// Initialize when DOM is ready
document.addEventListener(
  "DOMContentLoaded",
  initNavbarEffects,
  setupAIFeatures,
);
