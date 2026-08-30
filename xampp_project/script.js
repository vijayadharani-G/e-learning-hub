/* ==========================================================================
   E-Learning Hub Academy: Clean Client-Side Interactive Script for XAMPP
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
  // 1. Course Player Syllabus Video Switching
  const sidebarRows = document.querySelectorAll(".sidebar-btn");
  const playBtns = document.querySelectorAll(".sidebar-btn-play");
  const lecturePlayerEl = document.getElementById("lecture-player");
  const lectureTitle = document.getElementById("active-lecture-title");
  const lectureWeek = document.getElementById("active-lecture-week");
  const notesTextarea = document.getElementById("lecture-notes-textarea");
  const notesActiveLabel = document.getElementById("notes-active-label");
  const autoplayToast = document.getElementById("autoplay-next-toast");
  const autoplayToastText = document.getElementById("autoplay-next-toast-text");

  const notesData = window.NOTES_DATA || {};
  // Start on the resume week (first incomplete week, computed server-side)
  // instead of always week 0, so notes/tab state line up with what the sidebar
  // and video player already show on page load.
  let currentWeekIndex = typeof window.RESUME_INDEX === "number" ? window.RESUME_INDEX : 0;

  const loadNoteForIndex = (index) => {
    if (!notesTextarea) return;
    notesTextarea.value = notesData[index] || notesData[String(index)] || "";
    if (notesActiveLabel) {
      notesActiveLabel.textContent = "Notes — Week " + (index + 1);
    }
  };

  // ==========================================================================
  // Live video watch-percentage tracking
  // ==========================================================================
  // Shows a badge next to the lecture title with the live % of the current
  // video the student has actually watched (based on YouTube player
  // getCurrentTime()/getDuration()). Once the watched percentage crosses the
  // completion threshold, the badge turns green ("✓ Completed") and the
  // lecture is auto-marked complete via markWeekComplete() — the same
  // function used by the manual checkbox and the video-ended handler, so
  // there's a single source of truth for "how do I mark a week done".
  //
  // FIX: this interval now ALSO polls ytPlayer.getPlayerState() directly on
  // every tick instead of relying solely on the onStateChange callback.
  // On http://localhost, the YouTube widget's internal postMessage calls
  // report a target origin of "https://www.youtube.com", which doesn't match
  // the page's own origin ("http://localhost"). Browsers can silently drop
  // the resulting state-change postMessage in that situation, which means
  // onStateChange(ENDED) sometimes never fires — so a finished video was
  // never detected as complete, and the certificate never unlocked. Polling
  // getPlayerState() directly sidesteps that entirely, since it just reads
  // the player's current state instead of waiting for a message event.
  const WATCH_COMPLETE_THRESHOLD = 95; // percent; videos rarely hit exactly 100%
  const watchPercentBadge = document.getElementById("video-watch-percent");
  const completedWeeksFromServer = Array.isArray(window.COMPLETED_WEEKS) ? window.COMPLETED_WEEKS : [];

  let watchPercentInterval = null;
  let maxWatchedPercent = completedWeeksFromServer.includes(currentWeekIndex) ? 100 : 0;

  function updateWatchPercentUI(pct, isComplete) {
    if (!watchPercentBadge) return;
    const shown = Math.min(100, Math.round(pct));
    watchPercentBadge.textContent = isComplete ? "✓ Completed" : shown + "% watched";
    watchPercentBadge.classList.toggle("complete", isComplete);
  }

  function stopWatchPercentTracking() {
    if (watchPercentInterval) {
      clearInterval(watchPercentInterval);
      watchPercentInterval = null;
    }
  }

  function startWatchPercentTracking() {
    stopWatchPercentTracking();
    watchPercentInterval = setInterval(() => {
      if (!ytPlayer || typeof ytPlayer.getCurrentTime !== "function") return;

      // NEW: direct state poll — safety net in case the ENDED
      // state-change event got dropped due to the origin mismatch above.
      if (typeof ytPlayer.getPlayerState === "function") {
        const state = ytPlayer.getPlayerState();
        if (state === YT.PlayerState.ENDED) {
          handleLectureEnded();
          return;
        }
      }

      const duration = ytPlayer.getDuration();
      const current = ytPlayer.getCurrentTime();
      if (!duration) return;

      const pct = (current / duration) * 100;
      // Watched % only ever moves forward — seeking backward to rewatch a
      // part doesn't reduce it, and jumping ahead still requires the
      // threshold to have actually been reached before counting as done.
      if (pct > maxWatchedPercent) {
        maxWatchedPercent = pct;
      }

      if (maxWatchedPercent >= WATCH_COMPLETE_THRESHOLD) {
        updateWatchPercentUI(100, true);
        stopWatchPercentTracking();
        markWeekComplete(currentWeekIndex, playNextWeek);
      } else {
        updateWatchPercentUI(maxWatchedPercent, false);
      }
    }, 2000);
  }

  // Initial paint on page load, for the resume lecture.
  updateWatchPercentUI(maxWatchedPercent, maxWatchedPercent >= 100);

  // ---- Trackable video player (YouTube IFrame API), with a plain-iframe
  // fallback for non-YouTube links, blocked API scripts, or embed errors ----
  let ytPlayer = null;
  let ytPlayerReady = false;
  let fallbackTimeoutId = null;
  const initialVideoSrc = lecturePlayerEl ? lecturePlayerEl.getAttribute("data-video-src") : null;

  const extractYouTubeId = (url) => {
    if (!url) return null;
    const patterns = [
      /(?:youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/,
      /(?:youtube\.com\/watch\?v=)([a-zA-Z0-9_-]{11})/,
      /(?:youtu\.be\/)([a-zA-Z0-9_-]{11})/
    ];
    for (const p of patterns) {
      const m = url.match(p);
      if (m) return m[1];
    }
    return null;
  };

  // Plain, guaranteed-to-work iframe embed — used whenever the trackable
  // YouTube player isn't available or ready (auto-tick just won't fire for
  // videos shown this way, but playback always works).
  const showFallbackIframe = (src) => {
    if (!lecturePlayerEl) return;
    lecturePlayerEl.innerHTML = src
      ? '<iframe src="' + src + '" allowfullscreen style="width:100%;height:100%;border:0;"></iframe>'
      : "";
  };

  // Renders a given video src into the player. If the trackable YouTube
  // player is already up and running, it just swaps the video inside it
  // (keeps auto-tick tracking alive across sidebar clicks). Otherwise it
  // falls back to a plain iframe so the video is never stuck not playing.
  const loadVideo = (src) => {
    if (!lecturePlayerEl) return;
    const videoId = extractYouTubeId(src);
    if (ytPlayer && ytPlayerReady && videoId && typeof ytPlayer.loadVideoById === "function") {
      ytPlayer.loadVideoById(videoId);
    } else {
      showFallbackIframe(src);
    }
  };

  // Give the YouTube API a few seconds to load and initialize. If it hasn't
  // by then (no internet on this machine, ad-blocker, script blocked, etc.),
  // fall back to a plain embed so the video still plays — just without
  // auto-tick / auto-advance for that lecture.
  fallbackTimeoutId = setTimeout(() => {
    if (!ytPlayerReady) {
      showFallbackIframe(initialVideoSrc);
    }
  }, 4000);

  const showAutoplayToast = (text) => {
    if (!autoplayToast) return;
    if (autoplayToastText) autoplayToastText.textContent = text;
    autoplayToast.classList.add("visible");
    setTimeout(() => autoplayToast.classList.remove("visible"), 3200);
  };

  // Shows a floating "Download Certificate" button the instant all
  // weeks are complete — no page reload needed, no dependency on the PHP
  // re-check on next page load. Clicking it still goes through
  // certificate.php, which independently re-verifies completion server-side.
  //
  // ALSO fires a blocking confirm() popup the moment completion happens, so
  // the student can't miss it (the floating button alone is easy to miss if
  // they're not looking at the bottom-right corner). Guarded by the same
  // "already shown" check as the button, so it only ever fires once per
  // page load — clicking "Cancel" just leaves the floating button/banner
  // for them to use later instead of forcing a redirect.
  function showCertificateButton() {
    if (document.getElementById("instant-cert-btn")) return; // already shown

    const btn = document.createElement("a");
    btn.id = "instant-cert-btn";
    btn.href = "certificate.php?course_id=" + encodeURIComponent(window.COURSE_ID || "");
    btn.textContent = "🎓 Download Your Certificate";
    btn.className = "btn-primary";
    btn.style.cssText =
      "position:fixed;bottom:24px;right:24px;z-index:1000;" +
      "padding:14px 22px;border-radius:10px;font-size:14px;font-weight:700;" +
      "box-shadow:0 8px 24px rgba(22,163,74,0.4);background:linear-gradient(135deg,#16a34a,#15803d);" +
      "color:white;text-decoration:none;display:flex;align-items:center;gap:8px;";

    document.body.appendChild(btn);

    if (autoplayToast) {
      if (autoplayToastText) autoplayToastText.textContent = "🎓 Course complete — certificate ready!";
      autoplayToast.classList.add("visible");
      setTimeout(() => autoplayToast.classList.remove("visible"), 3200);
    }

    // NEW: blocking popup so completion is impossible to miss.
    const wantsCert = window.confirm(
      "🎉 Congratulations! You've completed all weeks of this course.\n\n" +
      "Click OK to download your certificate now, or Cancel to keep watching " +
      "(you can download it anytime from the button in the bottom-right corner)."
    );
    if (wantsCert) {
      window.location.href = btn.href;
    }
  }

  // Jumps to the next lecture in the sidebar by simulating a click on its
  // play button — reuses all the existing "switch lecture" logic below
  // instead of duplicating it.
  const playNextWeek = () => {
    const nextIndex = currentWeekIndex + 1;
    const nextBtn = document.querySelector('.sidebar-btn-play[data-index="' + nextIndex + '"]');
    if (!nextBtn) {
      showAutoplayToast("🎉 That was the last lecture — course complete!");
      return; // already on the final week, nothing to auto-advance to
    }
    showAutoplayToast("Lecture complete — playing Week " + (nextIndex + 1) + "…");
    nextBtn.click();
  };

  // Called when the YouTube player reports the current video has ended
  // (either via the onStateChange event, or via the direct state-poll
  // fallback in startWatchPercentTracking). Marks the week complete
  // (idempotent — safe even if already ticked), then auto-advances to
  // the next lecture.
  const handleLectureEnded = () => {
    stopWatchPercentTracking();
    updateWatchPercentUI(100, true);
    markWeekComplete(currentWeekIndex, playNextWeek);
  };

  window.onYouTubeIframeAPIReady = function () {
    const videoId = extractYouTubeId(initialVideoSrc);
    if (!videoId || !lecturePlayerEl) {
      showFallbackIframe(initialVideoSrc);
      return;
    }

    ytPlayer = new YT.Player("lecture-player", {
      videoId: videoId,
      playerVars: {
        rel: 0,
        // Explicitly declare this page's origin so the YouTube player's
        // internal postMessage calls target the right window and don't throw
        // "target origin does not match" console warnings (and, in some
        // browsers, silently drop state-change events like ENDED).
        // NOTE: on http://localhost this warning can still appear and
        // ENDED can still get dropped — that's why startWatchPercentTracking()
        // now also polls getPlayerState() directly as a safety net (see above).
        origin: window.location.origin,
        enablejsapi: 1
      },
      events: {
        onReady: () => {
          ytPlayerReady = true;
          if (fallbackTimeoutId) {
            clearTimeout(fallbackTimeoutId);
            fallbackTimeoutId = null;
          }
          // NEW: start the polling loop as soon as the player is ready,
          // instead of waiting for a PLAYING state-change event that
          // might itself get dropped by the same postMessage issue.
          if (maxWatchedPercent < WATCH_COMPLETE_THRESHOLD) {
            startWatchPercentTracking();
          }
        },
        onStateChange: (event) => {
          if (event.data === YT.PlayerState.PLAYING) {
            // Don't restart tracking (and don't reset maxWatchedPercent) if
            // this lecture was already marked complete — badge just stays green.
            if (maxWatchedPercent < WATCH_COMPLETE_THRESHOLD) {
              startWatchPercentTracking();
            }
          } else if (event.data === YT.PlayerState.PAUSED) {
            // Keep polling even while paused so a dropped ENDED event still
            // gets caught if the state flips without a clean postMessage.
            // (No-op here on purpose — startWatchPercentTracking already
            // covers detection; we simply don't stop it on pause anymore.)
          } else if (event.data === YT.PlayerState.ENDED) {
            handleLectureEnded();
          }
        },
        // Covers videos that fail inside the trackable player (embedding
        // disabled by the video owner, region restrictions, removed videos,
        // etc.) — fall back to a plain iframe so the student can still try
        // to watch it directly on YouTube's own embed.
        onError: () => {
          ytPlayerReady = false;
          showFallbackIframe(initialVideoSrc);
        }
      }
    });
  };

  playBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const parentRow = btn.closest(".sidebar-btn");

      // Deactivate all rows, activate the clicked one
      sidebarRows.forEach((row) => row.classList.remove("active"));
      if (parentRow) parentRow.classList.add("active");

      // Extract attributes
      const videoSrc = btn.getAttribute("data-video");
      const title = btn.getAttribute("data-topic");
      const week = btn.getAttribute("data-week");
      const index = parseInt(btn.getAttribute("data-index"), 10) || 0;

      currentWeekIndex = index;

      // Reset the watch-percentage badge for the newly selected lecture.
      // If this week was already ticked complete (server-rendered checkmark,
      // or completed earlier this session), show it as 100%/green right away
      // instead of restarting from 0%.
      stopWatchPercentTracking();
      const checkEl = document.querySelector('.sidebar-btn-check[data-index="' + index + '"]');
      const alreadyDone = !!(checkEl && checkEl.classList.contains("done"));
      maxWatchedPercent = alreadyDone ? 100 : 0;
      updateWatchPercentUI(maxWatchedPercent, alreadyDone);

      // Update player
      loadVideo(videoSrc);
      if (lectureTitle && title) {
        lectureTitle.textContent = title;
      }
      if (lectureWeek && week) {
        lectureWeek.textContent = week.toUpperCase();
      }

      // Sync notes panel to the newly selected lecture
      loadNoteForIndex(index);

      // NEW: restart the polling loop for the newly selected lecture
      // (loadVideo swaps the video inside the same player instance, so the
      // old interval would otherwise keep tracking against the new video —
      // this just makes sure state detection stays correct after a switch).
      if (ytPlayer && ytPlayerReady && !alreadyDone) {
        startWatchPercentTracking();
      }
    });
  });

  // Preload notes for the initially active (resume) lecture
  loadNoteForIndex(currentWeekIndex);

  // 2. Tab Navigation inside Course Detail Player
  const tabBtns = document.querySelectorAll(".tab-btn");
  const tabPanes = document.querySelectorAll(".tab-pane");

  tabBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const targetTab = btn.getAttribute("data-tab");

      // Toggle active states on tabs
      tabBtns.forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      // Toggle active states on content pane
      tabPanes.forEach((pane) => {
        pane.classList.remove("active");
        if (pane.id === `tab-${targetTab}`) {
          pane.classList.add("active");
        }
      });
    });
  });

  // 3. Live Interactive Sandbox Code Runner
  const runCodeBtn = document.getElementById("run-sandbox-btn");
  const resetCodeBtn = document.getElementById("reset-sandbox-btn");
  const codeTextarea = document.getElementById("sandbox-code-editor");
  const browserPreview = document.getElementById("sandbox-browser-preview");

  if (runCodeBtn && codeTextarea && browserPreview) {
    // Keep initial code for reset
    const originalCode = codeTextarea.value;

    runCodeBtn.addEventListener("click", () => {
      const currentCode = codeTextarea.value;

      // Render inside browser preview div securely
      browserPreview.innerHTML = currentCode;

      // Execute embedded scripts manually inside preview if needed
      const scripts = browserPreview.getElementsByTagName("script");
      for (let i = 0; i < scripts.length; i++) {
        try {
          eval(scripts[i].innerText);
        } catch (err) {
          console.error("Sandbox JS Error:", err);
        }
      }
    });

    if (resetCodeBtn) {
      resetCodeBtn.addEventListener("click", () => {
        codeTextarea.value = originalCode;
        browserPreview.innerHTML = "<div style='text-align: center; color: #a0aec0; padding-top: 50px;'>Click &apos;Run Code&apos; to view output here</div>";
      });
    }
  }

  // 4. Lecture Progress Tracking (mark complete / incomplete per week)
  const progressChecks = document.querySelectorAll(".sidebar-btn-check");
  const progressCountText = document.getElementById("progress-count-text");
  const progressFillBar = document.getElementById("progress-fill-bar");
  const totalWeeks = window.TOTAL_WEEKS || progressChecks.length;

  // Shared helper — always sets a week to COMPLETE (not a toggle) and
  // updates the tick/progress bar UI. Used by both the video-ended /
  // watch-percentage auto-complete flow and the manual checkbox click.
  // Safely callable multiple times (idempotent: skips the network call if
  // the week is already ticked).
  function markWeekComplete(index, callback) {
    const check = document.querySelector('.sidebar-btn-check[data-index="' + index + '"]');

    if (check && check.classList.contains("done")) {
      if (callback) callback();
      return;
    }

    const formData = new URLSearchParams();
    formData.append("course_id", window.COURSE_ID || "");
    formData.append("week_index", index);
    formData.append("completed", "1");
    formData.append("total_weeks", totalWeeks);

    fetch("progress%20handler.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: formData.toString()
    })
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          console.error("Progress update failed:", data.error);
          if (callback) callback();
          return;
        }

        if (check) check.classList.add("done");

        // If this update was for the lecture currently on screen, make sure
        // the watch-percentage badge also reflects "complete" (covers the
        // manual-checkbox path, which doesn't go through the video tracker).
        if (index === currentWeekIndex) {
          maxWatchedPercent = 100;
          updateWatchPercentUI(100, true);
        }

        if (progressCountText) {
          progressCountText.textContent = `${data.completed_count}/${data.total_weeks} Completed`;
        }
        if (progressFillBar) {
          const pct = data.total_weeks > 0 ? Math.round((data.completed_count / data.total_weeks) * 100) : 0;
          progressFillBar.style.width = pct + "%";
        }

        // Course just became fully complete: show the certificate button
        // immediately, right here in the DOM — no reload required.
        if (data.all_complete) {
          showCertificateButton();
          if (callback) callback();
          return;
        }

        if (callback) callback();
      })
      .catch((err) => {
        console.error("Progress request error:", err);
        if (callback) callback();
      });
  }

  progressChecks.forEach((check) => {
    check.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      const index = parseInt(check.getAttribute("data-index"), 10) || 0;
      const willBeCompleted = !check.classList.contains("done");

      const formData = new URLSearchParams();
      formData.append("course_id", window.COURSE_ID || "");
      formData.append("week_index", index);
      formData.append("completed", willBeCompleted ? "1" : "0");
      formData.append("total_weeks", totalWeeks);

      fetch("progress%20handler.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString()
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) {
            console.error("Progress update failed:", data.error);
            return;
          }

          check.classList.toggle("done", willBeCompleted);

          // Keep the watch-percentage badge for the on-screen lecture in
          // sync if the student manually (un)ticks it.
          if (index === currentWeekIndex) {
            maxWatchedPercent = willBeCompleted ? 100 : 0;
            updateWatchPercentUI(maxWatchedPercent, willBeCompleted);
          }

          if (progressCountText) {
            progressCountText.textContent = `${data.completed_count}/${data.total_weeks} Completed`;
          }
          if (progressFillBar) {
            const pct = data.total_weeks > 0 ? Math.round((data.completed_count / data.total_weeks) * 100) : 0;
            progressFillBar.style.width = pct + "%";
          }

          // Course just became fully complete: show the certificate button
          // immediately instead of reloading the page.
          if (data.all_complete) {
            showCertificateButton();
          }
        })
        .catch((err) => console.error("Progress request error:", err));
    });
  });

  // 5. Notes: Save current lecture note via AJAX
  const saveNotesBtn = document.getElementById("save-notes-btn");
  const notesSaveStatus = document.getElementById("notes-save-status");

  if (saveNotesBtn && notesTextarea) {
    saveNotesBtn.addEventListener("click", () => {
      const formData = new URLSearchParams();
      formData.append("course_id", window.COURSE_ID || "");
      formData.append("week_index", currentWeekIndex);
      formData.append("note_text", notesTextarea.value);

      fetch("notes%20handler.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString()
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.success) {
            notesData[currentWeekIndex] = notesTextarea.value;
            if (notesSaveStatus) {
              notesSaveStatus.classList.add("visible");
              setTimeout(() => notesSaveStatus.classList.remove("visible"), 2000);
            }
          } else {
            console.error("Note save failed:", data.error);
          }
        })
        .catch((err) => console.error("Note save request error:", err));
    });
  }

  // 6. Star rating input + review submit (Reviews tab)
  const starButtons = document.querySelectorAll("#star-rating-input button");
  const reviewCommentTextarea = document.getElementById("review-comment-textarea");
  const saveReviewBtn = document.getElementById("save-review-btn");
  const reviewSaveStatus = document.getElementById("review-save-status");
  const reviewsList = document.getElementById("reviews-list");

  let selectedRating = window.MY_REVIEW_RATING || 0;

  const paintStars = (upTo) => {
    starButtons.forEach((btn) => {
      const val = parseInt(btn.getAttribute("data-star"), 10);
      btn.classList.toggle("active", val <= upTo);
    });
  };

  if (starButtons.length) {
    paintStars(selectedRating);

    starButtons.forEach((btn) => {
      const val = parseInt(btn.getAttribute("data-star"), 10);

      btn.addEventListener("mouseenter", () => paintStars(val));
      btn.addEventListener("mouseleave", () => paintStars(selectedRating));
      btn.addEventListener("click", () => {
        selectedRating = val;
        paintStars(selectedRating);
      });
    });
  }

  if (saveReviewBtn) {
    saveReviewBtn.addEventListener("click", () => {
      if (!selectedRating) {
        if (reviewSaveStatus) {
          reviewSaveStatus.textContent = "Pick a star rating first!";
          reviewSaveStatus.style.color = "#ef4444";
          reviewSaveStatus.classList.add("visible");
          setTimeout(() => reviewSaveStatus.classList.remove("visible"), 2200);
        }
        return;
      }

      const formData = new URLSearchParams();
      formData.append("course_id", window.COURSE_ID || "");
      formData.append("rating", selectedRating);
      formData.append("comment", reviewCommentTextarea ? reviewCommentTextarea.value : "");

      fetch("review_handler.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: formData.toString()
      })
        .then((res) => res.json())
        .then((data) => {
          if (!data.success) {
            console.error("Review save failed:", data.error);
            return;
          }

          if (reviewSaveStatus) {
            reviewSaveStatus.textContent = "Saved!";
            reviewSaveStatus.style.color = "#16a34a";
            reviewSaveStatus.classList.add("visible");
            setTimeout(() => reviewSaveStatus.classList.remove("visible"), 2000);
          }
          if (saveReviewBtn) saveReviewBtn.textContent = "Update Review";

          // Refresh the review list + summary so the new/edited review shows immediately
          if (reviewsList && data.reviews_html !== undefined) {
            reviewsList.innerHTML = data.reviews_html;
          }
          const rsScore = document.querySelector(".rs-score");
          const rsStars = document.querySelector(".rs-stars");
          const rsCount = document.querySelector(".rs-count");
          if (rsScore && data.avg_rating !== undefined) rsScore.textContent = data.avg_rating.toFixed(1);
          if (rsStars && data.avg_rating !== undefined) {
            const rounded = Math.round(data.avg_rating);
            rsStars.textContent = "★".repeat(rounded) + "☆".repeat(5 - rounded);
          }
          if (rsCount && data.review_count !== undefined) {
            rsCount.textContent = `${data.review_count} student review${data.review_count === 1 ? "" : "s"}`;
          }
        })
        .catch((err) => console.error("Review save request error:", err));
    });
  }
});