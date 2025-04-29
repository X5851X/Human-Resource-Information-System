<template>
  <div class="layout">
    <!-- Navbar - tightly aligned with no spacing issues -->
    <header class="navbar">
      <div class="left-section">
        <img src="/public/images/logo-bekasi.png" alt="Logo Bekasi" class="logo" />
        <img src="/public/images/logo-diskominfostandi.png" alt="Logo Diskominfostandi" class="logo-disko" />
        <div class="system-title">SISDM Bekasi</div>
      </div>
      <div class="right-section">
        <div class="date-display">
          <CalendarDays class="calendar-icon" />
            <span class="current-date">Senin, 3 Februari 2025</span>
        </div>
        <div class="user-menu">
          <UserCircle class="user-icon" />
          <span class="admin-text">Hello, Admin</span>
          <button @click="logout" class="logout-btn">Logout</button>
        </div>
      </div>
    </header>

    <div class="main-container">
      <!-- Sidebar - flush with navbar and content -->
      <aside :class="['sidebar', { collapsed }]">
        <div class="sidebar-title">
          <Database class="db-icon" />
          <span v-if="!collapsed">SISTEM INFORMASI SDM</span>
        </div>
        <nav class="nav-links">
          <router-link to="/dashboard" active-class="active">
            <LayoutDashboard class="icon" />
            <span v-if="!collapsed">Dashboard</span>
          </router-link>
          <router-link to="/upload" active-class="active">
            <UploadCloud class="icon" />
            <span v-if="!collapsed">Upload</span>
          </router-link>
          <router-link to="/employee" active-class="active">
            <Users class="icon" />
            <span v-if="!collapsed">Employee</span>
          </router-link>
          <router-link to="/attendance" active-class="active">
            <CalendarCheck class="icon" />
            <span v-if="!collapsed">Attendance</span>
          </router-link>
          <router-link to="/reports" active-class="active">
            <FileText class="icon" />
            <span v-if="!collapsed">Reports</span>
          </router-link>
        </nav>
        <div class="sidebar-footer">
          <div class="version-info" v-if="!collapsed">SISDM v2.1.0</div>
          <button class="collapse-btn" @click="toggleSidebar">
            <ChevronLeft v-if="!collapsed" class="icon" />
            <ChevronRight v-else class="icon" />
          </button>
        </div>
      </aside>

      <!-- Content Area with dynamic breadcrumb -->
      <main :class="['content', { 'content-expanded': collapsed }]">
        <div class="breadcrumb">
          <router-link to="/" class="home-link">
          <Home class="breadcrumb-icon" />
          </router-link>
          <span class="separator">/</span>
          <span class="current-page">{{ currentPageName }}</span>
        </div>
        
        <div class="content-wrapper">
          <router-view />
        </div>
        
        <footer class="main-footer">
          <div class="footer-content">
            <span>© 2025 Diskominfostandi Kota Bekasi. All rights reserved.</span>
            <span class="support">Technical Support: support@bekasikota.go.id</span>
          </div>
        </footer>
      </main>
    </div>
  </div>
</template>

<script>
import {
  LayoutDashboard,
  UploadCloud,
  Users,
  CalendarCheck,
  FileText,
  ChevronLeft,
  ChevronRight,
  CalendarDays,
  UserCircle,
  Database,
  Home,
  Menu
} from 'lucide-vue-next';

export default {
  name: "MainLayout",
  components: {
    LayoutDashboard,
    UploadCloud,
    Users,
    CalendarCheck,
    FileText,
    ChevronLeft,
    ChevronRight,
    CalendarDays,
    UserCircle,
    Database,
    Home,
    Menu
  },
  data() {
    return {
      collapsed: false,
      routes: {
        dashboard: 'Dashboard',
        upload: 'Upload',
        employee: 'Employee',
        attendance: 'Attendance',
        reports: 'Reports'
      }
    };
  },
  computed: {
    formattedDate() {
      const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
      const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      
      const now = new Date();
      const day = days[now.getDay()];
      const date = now.getDate();
      const month = months[now.getMonth()];
      const year = now.getFullYear();
      
      return `${day}, ${date} ${month} ${year}`;
    },
    currentRouteName() {
      return this.$route.path.split('/')[1] || 'dashboard';
    },
    currentPageName() {
      return this.routes[this.currentRouteName] || 'Dashboard';
    }
  },
  mounted() {
    // Check for saved sidebar state
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState !== null) {
      this.collapsed = savedState === 'true';
    }
    
    // Add hamburger menu for mobile
    if (window.innerWidth <= 768) {
      this.collapsed = true;
    }
    
    // Listen for window resize
    window.addEventListener('resize', this.handleResize);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.handleResize);
  },
  methods: {
    logout() {
      window.location.href = "/";
    },
    toggleSidebar() {
      this.collapsed = !this.collapsed;
      localStorage.setItem('sidebarCollapsed', this.collapsed);
    },
    handleResize() {
      if (window.innerWidth <= 768) {
        this.collapsed = true;
      }
    }
  }
};
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  overflow: hidden;
  background-color: #fff;
}

.layout {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

/* Navbar - tightly aligned */
.navbar {
  height: 60px;
  background: linear-gradient(90deg, #5079a1 0%, #001a33 100%);
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1rem;
  z-index: 100;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}

.left-section {
  display: flex;
  align-items: center;
}

.logo {
  height: 40px;
  margin-right: 6px;
}

.logo-disko {
  height: 36px;
  margin-right: 10px;
}

.system-title {
  font-weight: 700;
  font-size: 1.1rem;
  letter-spacing: 0.5px;
  color: #fff;
}

.right-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.date-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background-color: rgba(255, 255, 255, 0.1);
  padding: 0.3rem 0.75rem;
  border-radius: 4px;
}

.calendar-icon {
  width: 16px;
  height: 16px;
}

.current-date {
  font-size: 0.85rem;
  font-weight: 500;
}

.user-menu {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-icon {
  width: 20px;
  height: 20px;
}

.admin-text {
  font-weight: 500;
  font-size: 0.9rem;
}

.logout-btn {
  background-color: #e53935;
  color: white;
  border: none;
  padding: 0.35rem 1rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background-color: #c62828;
}

/* Main container - no gaps */
.main-container {
  display: flex;
  height: calc(100vh - 60px);
  width: 100%;
}

/* Sidebar - flush with everything */
.sidebar {
  width: 250px;
  background: #0a2945;
  color: white;
  display: flex;
  flex-direction: column;
  height: 100%;
  transition: width 0.3s ease;
  z-index: 99;
  /* Fixed width to ensure alignment */
  flex-shrink: 0; 
}

.sidebar.collapsed {
  width: 64px;
}

.sidebar-title {
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  height: 50px;
}

.sidebar-title span {
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.db-icon {
  width: 18px;
  height: 18px;
  min-width: 18px;
}

.nav-links {
  display: flex;
  flex-direction: column;
  padding: 0.75rem 0.5rem;
  flex: 1;
}

.nav-links a {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  color: #e0e0e0;
  padding: 0.75rem 0.75rem;
  border-radius: 6px;
  text-decoration: none;
  transition: all 0.2s;
  margin-bottom: 2px;
}

.nav-links a:hover {
  background-color: rgba(255, 255, 255, 0.08);
}

.nav-links a.active {
  background-color: #007cc7;
  color: white;
  font-weight: 600;
}

.icon {
  width: 20px;
  height: 20px;
  min-width: 20px;
}

.sidebar-footer {
  padding: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.version-info {
  font-size: 0.75rem;
  opacity: 0.7;
}

.collapse-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.collapse-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* Content area - flush with sidebar */
.content {
  flex: 1;
  background-color: #f5f7fa;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: margin-left 0.3s ease;
}

.content-expanded {
  margin-left: 0;
}

.breadcrumb {
  padding: 0.75rem 1.25rem;
  background-color: #f0f2f5;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #4a5568;
  border-bottom: 1px solid #e5e7eb;
  height: 40px;
}

.breadcrumb-icon {
  width: 16px;
  height: 16px;
}

.separator {
  color: #a0aec0;
}

.current-page {
  font-weight: 600;
  color: #2d3748;
}

.content-wrapper {
  padding: 1rem;
  flex: 1;
  overflow-y: auto;
  background-color: #fff;
}

.main-footer {
  padding: 0.75rem 1.25rem;
  background-color: #f0f2f5;
  border-top: 1px solid #e5e7eb;
  height: 40px; /* Matching height with breadcrumb for symmetry */
  display: flex;
  align-items: center;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: #64748b;
  width: 100%;
}

.support {
  font-weight: 500;
}

/* Mobile menu button */
.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  color: white;
  cursor: pointer;
}

/* Responsive design - improved for mobile */
@media (max-width: 768px) {
  .navbar {
    height: 56px;
    padding: 0 0.5rem;
  }
  
  .system-title {
    font-size: 0.9rem;
  }
  
  .date-display {
    display: none;
  }
  
  .logo-disko {
    display: none;
  }
  
  .admin-text {
    display: none;
  }
  
  .main-container {
    height: calc(100vh - 56px);
  }
  
  .sidebar {
    position: fixed;
    z-index: 99;
    left: -250px;
    width: 250px;
    height: calc(100vh - 56px);
    transition: left 0.3s ease, width 0.3s ease;
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
  }
  
  .sidebar.collapsed {
    left: 0;
    width: 64px;
  }
  
  .content {
    margin-left: 0 !important;
  }
  
  .footer-content {
    flex-direction: column;
    gap: 0.5rem;
    align-items: flex-start;
  }
  
  .mobile-menu-btn {
    display: block;
    margin-right: 0.5rem;
  }
  
  .sidebar-title {
    justify-content: space-between;
  }
  
  .main-footer {
    height: auto;
    min-height: 40px;
  }
}
</style>