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
        toggleBtn.textContent = "❯"; 
        toggleBtn.classList.add("hidden"); 
        setTimeout(() => {
            toggleBtn.style.right = "100%"; 
            toggleBtn.classList.remove("hidden");
        }, 500); 
    } else {
        toggleBtn.textContent = "❮";
        toggleBtn.style.right = "0"; 
    }
});