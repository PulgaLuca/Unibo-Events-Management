document.addEventListener('DOMContentLoaded', function() {
  const skillCards = document.querySelectorAll('.skill-card');
  const skillCount = document.getElementById('skillCount');
  const resetButton = document.getElementById('resetSkills');
  
  skillCards.forEach(card => {
    const skillId = card.dataset.skillId;
    const select = card.querySelector('.skill-level');
    
    // Toggle skill selection on card click
    card.addEventListener('click', function(e) {
      // Don't toggle if clicking on the select dropdown or button
      if (e.target.tagName === 'SELECT' || e.target.tagName === 'OPTION' || e.target.tagName === 'BUTTON') {
        return;
      }
      
      e.preventDefault();
      const currentValue = select.value;
      
      if (currentValue === '0') {
        // If not selected, select with default level 2
        select.value = '2';
      } else {
        // If already selected, deselect
        select.value = '0';
      }
      
      updateCardState(card, select);
      updateSkillCount();
    });
    
    // Update card state when level changes
    select.addEventListener('change', function(e) {
      e.stopPropagation();
      updateCardState(card, select);
      updateSkillCount();
    });
  });
  
  // Reset all selected skills
  if (resetButton) {
    resetButton.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (confirm('Are you sure you want to reset all selected skills?')) {
        skillCards.forEach(card => {
          const select = card.querySelector('.skill-level');
          select.value = '0';
          updateCardState(card, select);
        });
        updateSkillCount();
      }
    });
  }
  
  function updateCardState(card, select) {
    const level = select.value;
    const badge = card.querySelector('.level-badge');
    
    if (level !== '0') {
      card.classList.add('selected');
      if (badge) {
        badge.textContent = 'Level ' + level;
      } else {
        const newBadge = document.createElement('span');
        newBadge.className = 'level-badge badge bg-primary';
        newBadge.textContent = 'Level ' + level;
        card.querySelector('.d-flex').appendChild(newBadge);
      }
    } else {
      card.classList.remove('selected');
      if (badge) {
        badge.remove();
      }
    }
  }
  
  function updateSkillCount() {
    const selectedSkills = document.querySelectorAll('.skill-level').length;
    let count = 0;
    document.querySelectorAll('.skill-level').forEach(select => {
      if (select.value !== '0') {
        count++;
      }
    });
    skillCount.textContent = count;
  }
});
