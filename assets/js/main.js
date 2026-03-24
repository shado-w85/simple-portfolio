/* global bootstrap */
(() => {
  "use strict";

  const COOKIE_NAME = "cookie_consent";
  const COOKIE_MAX_AGE_DAYS = 180;

  function setCookie(name, value, days) {
    const maxAge = Math.max(1, Math.floor(days)) * 24 * 60 * 60;
    document.cookie = `${encodeURIComponent(name)}=${encodeURIComponent(value)}; Max-Age=${maxAge}; Path=/; SameSite=Lax`;
  }

  function getCookie(name) {
    const encoded = encodeURIComponent(name) + "=";
    const parts = document.cookie.split(";").map((p) => p.trim());
    for (const p of parts) {
      if (p.startsWith(encoded)) return decodeURIComponent(p.slice(encoded.length));
    }
    return null;
  }

  function initCookieBanner() {
    const banner = document.querySelector(".cookie-banner");
    if (!banner) return;

    const existing = getCookie(COOKIE_NAME);
    if (existing === "accept" || existing === "decline") return;

    banner.hidden = false;

    const acceptBtn = banner.querySelector("[data-cookie-accept]");
    const declineBtn = banner.querySelector("[data-cookie-decline]");

    acceptBtn?.addEventListener("click", () => {
      setCookie(COOKIE_NAME, "accept", COOKIE_MAX_AGE_DAYS);
      banner.hidden = true;
    });

    declineBtn?.addEventListener("click", () => {
      setCookie(COOKIE_NAME, "decline", COOKIE_MAX_AGE_DAYS);
      banner.hidden = true;
    });
  }

  function initCookieSettingsButtons() {
    document.querySelectorAll("[data-open-cookie-settings]").forEach((btn) => {
      btn.addEventListener("click", () => {
        // Simple UX: reopen banner and let user choose again.
        const banner = document.querySelector(".cookie-banner");
        if (!banner) return;
        banner.hidden = false;
      });
    });
  }

  function initSmoothScrolling() {
    document.addEventListener("click", (e) => {
      const a = e.target?.closest?.("a[href^='#']");
      if (!a) return;
      const id = a.getAttribute("href");
      if (!id || id === "#") return;
      const el = document.querySelector(id);
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({ behavior: "smooth", block: "start" });
      history.replaceState(null, "", id);
    });
  }

  function initActiveNav() {
    const navLinks = Array.from(document.querySelectorAll(".navbar .nav-link"))
      .filter((a) => a.getAttribute("href")?.startsWith("#"));
    if (navLinks.length === 0) return;

    const sectionById = new Map();
    for (const link of navLinks) {
      const id = link.getAttribute("href");
      if (!id) continue;
      const section = document.querySelector(id);
      if (section) sectionById.set(id, section);
    }
    if (sectionById.size === 0) return;

    const observer = new IntersectionObserver(
      (entries) => {
        const visible = entries
          .filter((x) => x.isIntersecting)
          .sort((a, b) => (b.intersectionRatio ?? 0) - (a.intersectionRatio ?? 0))[0];
        if (!visible) return;

        const id = "#" + visible.target.id;
        for (const link of navLinks) {
          const isActive = link.getAttribute("href") === id;
          link.classList.toggle("active", isActive);
          if (isActive) link.setAttribute("aria-current", "page");
          else link.removeAttribute("aria-current");
        }
      },
      { rootMargin: "-30% 0px -60% 0px", threshold: [0.05, 0.15, 0.25] }
    );

    for (const section of sectionById.values()) observer.observe(section);
  }

  function initAutoCloseMobileNav() {
    const nav = document.getElementById("siteNav");
    if (!nav) return;
    nav.addEventListener("click", (e) => {
      const a = e.target?.closest?.("a");
      if (!a) return;
      const toggler = document.querySelector(".navbar-toggler");
      const isExpanded = toggler?.getAttribute("aria-expanded") === "true";
      if (!isExpanded) return;

      // bootstrap is optional; this fails gracefully.
      try {
        const instance = bootstrap?.Collapse?.getOrCreateInstance?.(nav);
        instance?.hide?.();
      } catch {
        // no-op
      }
    });
  }

  initCookieBanner();
  initCookieSettingsButtons();
  initSmoothScrolling();
  initActiveNav();
  initAutoCloseMobileNav();
})();

