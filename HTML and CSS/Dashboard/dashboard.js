function checkVotingTime() {
      const now = new Date();
      const startTime = new Date();
      startTime.setHours(9, 25, 0, 0);  
      const endTime = new Date();
      endTime.setHours(11,0, 0, 0);   

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