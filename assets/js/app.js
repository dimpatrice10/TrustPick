// TrustPick UI interactions: light-weight animations + IntersectionObserver
document.addEventListener('DOMContentLoaded', function(){
  console.log('TrustPick UI loaded');

  // Reveal on scroll using IntersectionObserver
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e => {
      if(e.isIntersecting){
        e.target.classList.add('in-view');
        io.unobserve(e.target);
      }
    });
  },{threshold:0.12});

  document.querySelectorAll('.fade-up, .appear').forEach(el => io.observe(el));

  // Subtle micro-interaction for cards: elevate on hover handled by CSS, but add keyboard support
  document.querySelectorAll('.card').forEach(card => {
    card.setAttribute('tabindex','0');
    card.addEventListener('keydown', e => { if(e.key === 'Enter') card.click(); });
  });

  // Search quick demo: animate results (static)
  const search = document.querySelector('.search');
  if(search){
    search.addEventListener('input', (e) => {
      const val = e.target.value.trim();
      if(val.length > 1){
        search.style.boxShadow = '0 12px 40px rgba(11,94,215,0.12)';
      } else {
        search.style.boxShadow = '';
      }
    });
  }

  // Performance note: use transform/opacity only; avoid layout thrashing
});

