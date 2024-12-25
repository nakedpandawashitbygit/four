function toggleSidebar() {
	const sidebarEntity = document.getElementById('sidebar');
	const dashboardContent = document.getElementById('dasboard-content');
	const sidebarCollapse = document.getElementById('sidebar-collapse');
	const toggleIcon = document.getElementById('toggle-icon');
	
	sidebarEntity.classList.toggle('active');
	
	sidebarCollapse.classList.toggle('active');
	
	if (sidebarCollapse.classList.contains('active')) {
        toggleIcon.src = '/img/buttons/navigate_next.svg';
    } else {
        toggleIcon.src = '/img/buttons/navigate_before.svg';
    }
	
	dashboardContent.style.marginLeft = dashboardContent.style.marginLeft === '80px' ? '250px' : '80px';
}