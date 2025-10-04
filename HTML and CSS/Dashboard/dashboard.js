function checkVotingTime() {
      const now = new Date();
      const startTime = new Date();
      startTime.setHours(14, 25, 0, 0);  
      const endTime = new Date();
      endTime.setHours(15,0, 0, 0);   

      const voteButtons = document.querySelectorAll(".voteBtn");

      voteButtons.forEach(btn => {
        if (now >= startTime && now <= endTime) {
          btn.disabled = false;
        } else {
          btn.disabled = true;
        }
      });
    }
    checkVotingTime();
    setInterval(checkVotingTime, 10000);