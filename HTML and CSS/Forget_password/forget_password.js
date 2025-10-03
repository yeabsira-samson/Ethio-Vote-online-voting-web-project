const inputs = document.querySelectorAll('.otp-input');

inputs.forEach((input, index) => {

  // Validation: highlight empty input
  input.addEventListener('input', function() {
    if (this.value.trim() === "") {
      this.style.border = "2px solid red";
    } else {
      this.style.border = "2px solid green"; 
      if (index < inputs.length - 1) {
        inputs[index + 1].focus();
      }
    }
  });

  // Keyboard navigation
  input.addEventListener('keydown', function(e) {
    if (e.key === "ArrowLeft" && index > 0) {
      inputs[index - 1].focus();
    } else if (e.key === "ArrowRight" && index < inputs.length - 1) {
      inputs[index + 1].focus();
    } else if (e.key === "Backspace" && this.value === "" && index > 0) {
      inputs[index - 1].focus();
    }
  });
});
//timer
   let timerInterval;
  function showPanel(id) {
    document.querySelectorAll('.panel').forEach(panel => panel.classList.remove('active'));
    document.getElementById(id).classList.add('active');

    if (id === 'panel2') {
      startTimer(60); 
    }
  }

  function startTimer(seconds) {
    clearInterval(timerInterval); 
    const timerDisplay = document.getElementById("timer");
    let timeLeft = seconds;
    timerDisplay.textContent = timeLeft;

    timerInterval = setInterval(() => {
      timeLeft--;
      timerDisplay.textContent = timeLeft;
      if (timeLeft <= 0) {
        clearInterval(timerInterval);
        timerDisplay.textContent = "0";
      }
    }, 1000);
  }
 

  // Add this script to your page
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - looking for OTP link...');
    
    const sendLink = document.getElementById('sendOTPLink');
    
    if (sendLink) {
        console.log('OTP link found!');
        
        sendLink.addEventListener('click', function(event) {
            event.preventDefault();
            console.log('OTP link clicked!');
            sendOTP(event);
        });
        
    } else {
        console.error('OTP link not found! Check the ID "sendOTPLink"');
    }
});

// OTP function
async function sendOTP(event) {
    console.log('sendOTP function called');
    
    const sendLink = document.getElementById('sendOTPLink');
    const originalText = sendLink.innerHTML;
    
    // Show loading state
    sendLink.innerHTML = 'Sending OTP...';
    sendLink.style.pointerEvents = 'none';
    sendLink.style.opacity = '0.6';
    sendLink.style.textDecoration = 'none';
    
    try {
        console.log('Making request to resend.php...');
        
        const response = await fetch('resend.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            }
        });

        console.log('Response status:', response.status);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);

        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed result:', result);
        } catch (e) {
            console.error('JSON parse error:', e);
            throw new Error('Server returned invalid response');
        }

        if (result.status === 'success') {
            alert('✅ ' + result.message);
            console.log('OTP sent successfully');

        } else {
            alert('❌ ' + result.message);
            // Reset link on error
            resetOTPLink(sendLink, originalText);
        }

    } catch (error) {
        console.error('Error:', error);
        alert('❌ Failed to send OTP: ' + error.message);
        // Reset link on error
        resetOTPLink(sendLink, originalText);
    }
}

function resetOTPLink(link, originalText) {
    link.innerHTML = originalText;
    link.style.pointerEvents = 'auto';
    link.style.opacity = '1';
}
