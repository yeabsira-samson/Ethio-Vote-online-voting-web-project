function showpanel(panelId, event) {
    event.preventDefault(); 

    
    const panels = document.querySelectorAll('.panel');
    panels.forEach(panel => {
        panel.style.display = 'none';
    });
    
    const selectedPanel = document.getElementById(panelId);
    if (selectedPanel) {
        selectedPanel.style.display = 'block';
    }
}

const toggleBtn = document.getElementById("toggleBtn");
const aside = document.getElementById("result");

toggleBtn.addEventListener("click", () => {
    aside.classList.toggle("active");

    if (aside.classList.contains("active")) {
        toggleBtn.textContent = "❯"; // change arrow
        toggleBtn.classList.add("hidden"); // hide button when aside is open
        // show button again after aside slides in
        setTimeout(() => {
            toggleBtn.style.right = "100%"; // move outside aside
            toggleBtn.classList.remove("hidden");
        }, 500); // wait for slide animation
    } else {
        toggleBtn.textContent = "❮"; // reset arrow
        toggleBtn.style.right = "0"; // back to exact aside spot
    }
});