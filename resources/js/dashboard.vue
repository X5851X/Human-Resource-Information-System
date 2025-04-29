<template>
  <div class="container mx-auto py-6 px-4">
    <!-- Background patterns -->
    <div class="background-pattern pattern-dots"></div>
    <div class="background-pattern pattern-lines"></div>
    
    <div class="card">
      <div class="card-header">
        <h1 class="text-xl font-bold text-primary">Dashboard</h1>
      </div>

      <!-- Search and Filter Section -->
      <div class="search-filter-bar">
        <div class="date-navigation">
          <button @click="previousPeriod" class="nav-btn">
            ◀
          </button>
          <span class="date-display">{{ currentPeriod }}</span>
          <button @click="nextPeriod" class="nav-btn">
            ▶
          </button>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 10px;">
          <select v-model="selectedBidang" class="filter-select">
            <option value="">Semua Bidang</option>
            <option v-for="bidang in bidangList" :key="bidang" :value="bidang">{{ bidang }}</option>
          </select>
          <select v-model="viewMode" class="filter-select">
            <option value="weekly">Mingguan</option>
            <option value="monthly">Bulanan</option> 
            <option value="quarterly">Kuartal</option>
          </select>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="summary-container">
        <div class="summary-card">
          <div class="summary-icon summary-present">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <div class="summary-info">
            <h3 class="summary-title">Total Pegawai</h3>
            <p class="summary-value">81</p>
          </div>
        </div>
        
        <div class="summary-card">
          <div class="summary-icon summary-active">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
              <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
          </div>
          <div class="summary-info">
            <h3 class="summary-title">Tingkat Kehadiran</h3>
            <p class="summary-value">83%</p>
          </div>
        </div>
        
        <div class="summary-card">
          <div class="summary-icon summary-late">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
          </div>
          <div class="summary-info">
            <h3 class="summary-title">Jumlah Kegiatan</h3>
            <p class="summary-value">3</p>
          </div>
        </div>
        
        <div class="summary-card">
          <div class="summary-icon summary-absent">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
          </div>
          <div class="summary-info">
            <h3 class="summary-title">Total Peserta</h3>
            <p class="summary-value">64</p>
          </div>
        </div>
      </div>

      <!-- Attendance Chart -->
      <div class="chart-section animation-fade">
        <div class="chart-header">
          <h2 class="chart-title">Grafik Kehadiran</h2>
          <div class="chart-legend">
        <div class="legend-item">
          <span class="legend-color legend-present"></span>
          <span class="legend-text">Hadir</span>
        </div>
        <div class="legend-item">
          <span class="legend-color legend-absent"></span>
          <span class="legend-text">Tidak Hadir</span>
        </div>
          </div>
        </div>
        <div class="chart-container">
          <div class="chart-wrapper">
        <div class="chart-column">
          <div class="chart-bars">
            <div class="chart-bar chart-bar-present" :style="{ height: '83%' }"></div>
            <div class="chart-bar chart-bar-absent" :style="{ height: '17%' }"></div>
          </div>
          <div class="chart-label">Total Kehadiran</div>
        </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities and Top Employees -->
      <div class="dashboard-data-section">
        <div class="recent-attendance-section">
          <h2 class="section-title">Kegiatan Terbaru</h2>
          <div class="data-card">
            <table class="mini-table">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Nama Kegiatan</th>
                  <th>Jumlah Peserta</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(activity, index) in recentActivities" :key="index" 
                    :class="{'row-alternate': index % 2 === 0}">
                  <td>{{ formatDate(activity.tanggal_kegiatan) }}</td>
                  <td>{{ activity.nama_kegiatan }}</td>
                  <td class="text-center">{{ activity.jumlah_peserta }}</td>
                </tr>
                <tr v-if="loading">
                  <td colspan="3" class="empty-message">
                    <div class="empty-state">
                      <div class="loading-spinner"></div>
                      <p>Memuat data...</p>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="recentActivities.length === 0">
                  <td colspan="3" class="empty-message">
                    <div class="empty-state">
                      <div class="empty-icon">🔍</div>
                      <p>Tidak ada kegiatan yang ditemukan</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="top-employees-section">
          <h2 class="section-title">Peserta Aktif</h2>
          <div class="data-card">
            <div class="top-employee" v-for="(employee, index) in topEmployees" :key="index" 
                 :class="{'row-alternate': index % 2 === 0}">
              <div class="employee-rank">{{ index + 1 }}</div>
              <div class="employee-name">
                <div class="avatar" :style="{ backgroundColor: getAvatarColor(employee.nama) }">
                  {{ getInitials(employee.nama) }}
                </div>
                <div class="employee-details">
                  <span class="employee-fullname">{{ employee.nama }}</span>
                  <span class="employee-department">{{ employee.bidang || '—' }}</span>
                </div>
              </div>
              <div class="employee-stats">
                <span class="employee-stat">{{ employee.kegiatan_count }}</span>
                <span class="employee-stat-label">Kegiatan</span>
              </div>
            </div>
            <div v-if="loading" class="empty-message">
              <div class="empty-state">
                <div class="loading-spinner"></div>
                <p>Memuat data...</p>
              </div>
            </div>
            <div v-else-if="topEmployees.length === 0" class="empty-message">
              <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <p>Tidak ada data peserta yang ditemukan</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Department Performance -->
      <div class="department-section animation-fade">
        <h2 class="section-title">Kegiatan Per Bidang</h2>
        <div class="data-card">
          <div class="department-chart">
            <div v-for="(dept, index) in departmentPerformance" :key="index" class="department-row"
                 :class="{'row-alternate': index % 2 === 0}">
              <div class="department-name">{{ dept.name }}</div>
              <div class="department-bar-container">
                <div class="department-bar" :style="{ width: dept.percentage + '%', backgroundColor: getDeptColor(dept.percentage) }"></div>
              </div>
              <div class="department-value">{{ dept.count }} kegiatan</div>
            </div>
          </div>
          <div v-if="loading" class="empty-message">
            <div class="empty-state">
              <div class="loading-spinner"></div>
              <p>Memuat data...</p>
            </div>
          </div>
          <div v-else-if="departmentPerformance.length === 0" class="empty-message">
            <div class="empty-state">
              <div class="empty-icon">🔍</div>
              <p>Tidak ada data bidang yang ditemukan</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Export Button -->
      <div class="add-button-container">
        <button @click="showExportModal = true" class="btn-add">
          <span class="add-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="17 8 12 3 7 8"></polyline>
              <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
          </span>
          Export Laporan
        </button>
      </div>
    </div>

    <!-- Export Report Modal -->
    <div v-if="showExportModal" class="modal-backdrop">
      <div class="modal-container animation-slide-up">
        <div class="modal-header">
          <h3 class="modal-title">Export Laporan</h3>
          <button @click="showExportModal = false" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="exportReport" class="attendance-form">
            <div class="form-group">
              <label class="form-label">Format Laporan</label>
              <div class="report-format-options">
                <button 
                  type="button"
                  v-for="format in ['PDF', 'EXCEL', 'CSV']" 
                  :key="format" 
                  @click="selectedFormat = format" 
                  class="format-option" 
                  :class="{ 'selected': selectedFormat === format }">
                  {{ format }}
                </button>
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Periode</label>
              <div class="date-range-picker">
                <input type="date" class="form-input" v-model="exportDateRange.start" required />
                <span class="date-range-separator">sampai</span>
                <input type="date" class="form-input" v-model="exportDateRange.end" required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Opsi Laporan</label>
              <div class="checkbox-options">
                <label class="checkbox-container">
                  <input type="checkbox" v-model="exportOptions.includeCharts" />
                  <span class="checkmark"></span>
                  Sertakan Grafik
                </label>
                <label class="checkbox-container">
                  <input type="checkbox" v-model="exportOptions.includeEmployeeDetails" />
                  <span class="checkmark"></span>
                  Detail Per Peserta
                </label>
                <label class="checkbox-container">
                  <input type="checkbox" v-model="exportOptions.includeSummary" />
                  <span class="checkmark"></span>
                  Ringkasan Eksekutif
                </label>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button @click="showExportModal = false" class="btn-secondary">Batal</button>
          <button @click="exportReport" class="btn-primary">Export</button>
        </div>
      </div>
    </div>

    <!-- Success Message Alert -->
    <div v-if="successMessage" class="alert-floating alert-success animation-fade">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-4">
        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
        <polyline points="22 4 12 14.01 9 11.01"></polyline>
      </svg>
      {{ successMessage }}
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Dashboard',
  data() {
    return {
      loading: true,
      searchQuery: '',
      selectedBidang: '',
      viewMode: 'monthly',
      currentPeriod: 'April 2023',
      showExportModal: false,
      successMessage: '',
      
      // Export settings
      selectedFormat: 'PDF',
      exportDateRange: {
        start: new Date().toISOString().substr(0, 10),
        end: new Date().toISOString().substr(0, 10)
      },
      exportOptions: {
        includeCharts: true,
        includeEmployeeDetails: false,
        includeSummary: true
      },
      
      // Dashboard statistics (will be populated from API)
      dashboardStats: {
        totalEmployees: 0,
        attendanceRate: 0,
        totalActivities: 0,
        totalParticipants: 0
      },
      
      // Chart data (will be populated from API)
      chartData: [],
      
      // Recent activities data (will be populated from API)
      recentActivities: [],
      
      // Top employees data (will be populated from API)
      topEmployees: [],
      
      // Department performance data (will be populated from API)
      departmentPerformance: [],
      
      // Available bidang list based on our database structure
      bidangList: ['SEKRETARIAT', 'PIP', 'TIK', 'EGOVERMENT', 'STANDI']
    };
  },
  methods: {
    // Navigation methods
    previousPeriod() {
      // Calculate previous period based on viewMode
      const currentDate = new Date(this.getPeriodDate());
      if (this.viewMode === 'weekly') {
        currentDate.setDate(currentDate.getDate() - 7);
      } else if (this.viewMode === 'monthly') {
        currentDate.setMonth(currentDate.getMonth() - 1);
      } else if (this.viewMode === 'quarterly') {
        currentDate.setMonth(currentDate.getMonth() - 3);
      }
      this.updatePeriodDisplay(currentDate);
      this.fetchDashboardData(); // Reload data for the new period
    },
    
    nextPeriod() {
      // Calculate next period based on viewMode
      const currentDate = new Date(this.getPeriodDate());
      if (this.viewMode === 'weekly') {
        currentDate.setDate(currentDate.getDate() + 7);
      } else if (this.viewMode === 'monthly') {
        currentDate.setMonth(currentDate.getMonth() + 1);
      } else if (this.viewMode === 'quarterly') {
        currentDate.setMonth(currentDate.getMonth() + 3);
      }
      this.updatePeriodDisplay(currentDate);
      this.fetchDashboardData(); // Reload data for the new period
    },
    
    // Get date from current period string
    getPeriodDate() {
      // Parse the currentPeriod string back to a date
      const parts = this.currentPeriod.split(' ');
      const months = {
        'Januari': 0, 'Februari': 1, 'Maret': 2, 'April': 3, 'Mei': 4, 'Juni': 5,
        'Juli': 6, 'Agustus': 7, 'September': 8, 'Oktober': 9, 'November': 10, 'Desember': 11
      };
      
      // Handle quarterly format (e.g., "Q2 2023")
      if (parts[0].startsWith('Q')) {
        const quarter = parseInt(parts[0].substring(1));
        const year = parseInt(parts[1]);
        // Convert quarter to month (Q1=Jan, Q2=Apr, Q3=Jul, Q4=Oct)
        const month = (quarter - 1) * 3;
        return new Date(year, month, 1);
      } 
      // Handle weekly format (e.g., "1-7 April 2023")
      else if (parts[0].includes('-')) {
        const startDay = parseInt(parts[0].split('-')[0]);
        const month = months[parts[1]];
        const year = parseInt(parts[2]);
        return new Date(year, month, startDay);
      }
      // Handle monthly format (e.g., "April 2023")
      else {
        const year = parseInt(parts[1]);
        const month = months[parts[0]];
        return new Date(year, month, 1);
      }
    },
    
    // Update period display based on date and view mode
    updatePeriodDisplay(date) {
      const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ];
      
      if (this.viewMode === 'weekly') {
        // For weekly view, show range like "1-7 April 2023"
        const endDate = new Date(date);
        endDate.setDate(date.getDate() + 6);
        this.currentPeriod = `${date.getDate()}-${endDate.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
      } else if (this.viewMode === 'monthly') {
        // For monthly view, show "April 2023"
        this.currentPeriod = `${months[date.getMonth()]} ${date.getFullYear()}`;
      } else if (this.viewMode === 'quarterly') {
        // For quarterly view, show "Q2 2023" (April-June is Q2)
        const quarter = Math.floor(date.getMonth() / 3) + 1;
        this.currentPeriod = `Q${quarter} ${date.getFullYear()}`;
      }
    },
    
    // Format date for display
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(dateString).toLocaleDateString('id-ID', options);
    },
    
    // Get initials from name
    getInitials(name) {
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    
    // Get avatar background color based on name
    getAvatarColor(name) {
      const colors = [
        '#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#34495e',
        '#16a085', '#27ae60', '#2980b9', '#8e44ad', '#2c3e50',
        '#f1c40f', '#e67e22', '#e74c3c', '#95a5a6', '#f39c12',
        '#d35400', '#c0392b', '#7f8c8d'
      ];
      
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      
      const index = Math.abs(hash % colors.length);
      return colors[index];
    },
    
    // Get department color based on performance value
    getDeptColor(value) {
      if (value >= 80) return '#2ecc71';
      if (value >= 60) return '#27ae60';
      if (value >= 40) return '#f39c12';
      if (value >= 20) return '#e67e22';
      return '#e74c3c';
    },
    
    // Export report
    exportReport() {
      this.loading = true;
      
      // Get period dates for the report
      const params = {
        start_date: this.exportDateRange.start,
        end_date: this.exportDateRange.end,
        format: this.selectedFormat.toLowerCase(),
        include_charts: this.exportOptions.includeCharts ? 1 : 0,
        include_employee_details: this.exportOptions.includeEmployeeDetails ? 1 : 0,
        include_summary: this.exportOptions.includeSummary ? 1 : 0
      };
      
      // Add bidang filter if selected
      if (this.selectedBidang) {
        params.bidang = this.selectedBidang;
      }
      
      axios.post('/api/reports/export', params, { responseType: 'blob' })
        .then(response => {
          // Create download link for the file
          const url = window.URL.createObjectURL(new Blob([response.data]));
          const link = document.createElement('a');
          link.href = url;
          
          // Set filename based on content-disposition header if available
          let filename = 'laporan.' + this.selectedFormat.toLowerCase();
          const disposition = response.headers['content-disposition'];
          if (disposition && disposition.indexOf('attachment') !== -1) {
            const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
            const matches = filenameRegex.exec(disposition);
            if (matches != null && matches[1]) {
              filename = matches[1].replace(/['"]/g, '');
            }
          }
          
          link.setAttribute('download', filename);
          document.body.appendChild(link);
          link.click();
          document.body.removeChild(link);
          
          this.showExportModal = false;
          this.showSuccessMessage(`Laporan berhasil diexport dalam format ${this.selectedFormat}`);
          this.loading = false;
        })
        .catch(error => {
          console.error('Error exporting report:', error);
          this.loading = false;
          alert('Terjadi kesalahan saat mengexport laporan. Silahkan coba lagi.');
        });
    },
    
    // Show success message
    showSuccessMessage(message) {
      this.successMessage = message;
      setTimeout(() => {
        this.successMessage = '';
      }, 3000);
    },
    
    // Fetch dashboard data from API
    fetchDashboardData() {
      this.loading = true;
      
      // Get period dates based on current period and view mode
      const periodStart = this.getPeriodDate();
      const periodEnd = new Date(periodStart);
      
      if (this.viewMode === 'weekly') {
        periodEnd.setDate(periodStart.getDate() + 6);
      } else if (this.viewMode === 'monthly') {
        periodEnd.setMonth(periodStart.getMonth() + 1);
        periodEnd.setDate(0); // Last day of the month
      } else if (this.viewMode === 'quarterly') {
        periodEnd.setMonth(periodStart.getMonth() + 3);
        periodEnd.setDate(0); // Last day of the quarter
      }
      
      // Format dates for API
      const startDate = periodStart.toISOString().split('T')[0];
      const endDate = periodEnd.toISOString().split('T')[0];
      
      // Set up the params object
      const params = {
        start_date: startDate,
        end_date: endDate,
        view_mode: this.viewMode
      };
      
      // Add bidang filter if selected
      if (this.selectedBidang) {
        params.bidang = this.selectedBidang;
      }
      
      // Fetch dashboard stats
      axios.get('/api/dashboard/stats', { params })
        .then(response => {
          this.dashboardStats = response.data;
        })
        .catch(error => {
          console.error('Error fetching dashboard stats:', error);
        });
      
      // Fetch chart data
      axios.get('/api/dashboard/chart', { params })
        .then(response => {
          this.chartData = response.data;
        })
        .catch(error => {
          console.error('Error fetching chart data:', error);
        });
      
      // Fetch recent activities
      axios.get('/api/dashboard/activities', { params })
        .then(response => {
          this.recentActivities = response.data;
        })
        .catch(error => {
          console.error('Error fetching recent activities:', error);
        });
      
      // Fetch top employees
      axios.get('/api/dashboard/employees', { params })
        .then(response => {
          this.topEmployees = response.data;
        })
        .catch(error => {
          console.error('Error fetching top employees:', error);
        });
      
      // Fetch department performance
      axios.get('/api/dashboard/departments', { params })
        .then(response => {
          this.departmentPerformance = response.data;
        })
        .catch(error => {
          console.error('Error fetching department performance:', error);
        })
        .finally(() => {
          this.loading = false;
        });
    }
  },
  watch: {
    // Watch for changes in view mode or bidang filter
    viewMode() {
      // Update period display format when view mode changes
      const currentDate = this.getPeriodDate();
      this.updatePeriodDisplay(currentDate);
      this.fetchDashboardData(); // Reload data for the new period
    },
    
    selectedBidang() {
      // Reload data when bidang filter changes
      this.fetchDashboardData();
    }
  },
  mounted() {
    // Initialize with current date and fetch data
    const today = new Date();
    this.updatePeriodDisplay(today);
    this.fetchDashboardData();
  }
}
</script>

<style scoped>
/* Container and general layout styles */
.container {
  position: relative;
  overflow: hidden;
}

.background-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: -1;
  opacity: 0.05;
}

.pattern-dots {
  background-image: radial-gradient(#3498db 1px, transparent 1px);
  background-size: 20px 20px;
}

.pattern-lines {
  background-size: 50px 50px;
  background-image: linear-gradient(to right, #e74c3c 1px, transparent 1px),
                    linear-gradient(to bottom, #e74c3c 1px, transparent 1px);
  opacity: 0.03;
}

.card {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
  padding: 20px;
  margin-bottom: 20px;
  position: relative;
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #f1f1f1;
}

.text-primary {
  color: #3498db;
}

/* Search and filter section */
.search-filter-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  padding: 10px 15px;
  background-color: #f8f9fa;
  border-radius: 8px;
}

.date-navigation {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-display {
  font-weight: 600;
  min-width: 150px;
  text-align: center;
}

.nav-btn {
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px;
  background-color: #f1f1f1;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-btn:hover {
  background-color: #e0e0e0;
}

.filter-wrapper {
  margin-left: auto; /* Push filters to the right */
  margin-right: 10px; /* Add spacing between filters */
  display: flex;
  align-items: center;
}

.filter-wrapper:last-child {
  margin-right: 0; /* Remove right margin from last filter */
}

.filter-select {
  padding: 8px 12px;
  border-radius: 4px;
  border: 1px solid #e0e0e0;
  background-color: #fff;
  min-width: 150px;
  cursor: pointer;
  appearance: none;
  padding-right: 30px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23333' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
}

/* Summary cards */
.summary-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.summary-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 15px;
  display: flex;
  align-items: center;
  transition: transform 0.2s;
}

.summary-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

.summary-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 15px;
  color: white;
}

.summary-present {
  background-color: #2ecc71;
}

.summary-active {
  background-color: #3498db;
}

.summary-late {
  background-color: #f39c12;
}

.summary-absent {
  background-color: #e74c3c;
}

.summary-info {
  flex: 1;
}

.summary-title {
  font-size: 0.875rem;
  color: #7f8c8d;
  margin-bottom: 5px;
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin: 0;
}

/* Chart section */
.chart-section {
  margin-bottom: 30px;
  padding: 20px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
  flex-wrap: wrap;
}

.chart-title {
  font-size: 1.125rem;
  color: #34495e;
  margin: 0;
}

.chart-legend {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 15px;
}

.legend-item {
  display: flex;
  align-items: center;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 3px;
  margin-right: 5px;
}

.legend-present {
  background-color: #2ecc71;
}

.legend-late {
  background-color: #f39c12;
}

.legend-absent {
  background-color: #e74c3c;
}

.legend-permit {
  background-color: #3498db;
}

.legend-text {
  font-size: 0.75rem;
  color: #7f8c8d;
}

.chart-container {
  height: 250px;
  position: relative;
}

.chart-wrapper {
  display: flex;
  height: 100%;
  align-items: flex-end;
  justify-content: space-between;
}

.chart-column {
  flex: 1;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  padding: 0 5px;
}

.chart-bars {
  width: 100%;
  height: 85%;
  display: flex;
  flex-direction: column-reverse;
  align-items: center;
}

.chart-bar {
  width: 70%;
  margin: 1px 0;
  border-radius: 3px;
  transition: height 0.5s;
}

.chart-bar-present {
  background-color: #2ecc71;
}

.chart-bar-late {
  background-color: #f39c12;
}

.chart-bar-absent {
  background-color: #e74c3c;
}

.chart-bar-permit {
  background-color: #3498db;
}

.chart-label {
  text-align: center;
  font-size: 0.75rem;
  color: #7f8c8d;
  margin-top: 8px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/* Dashboard data sections */
.dashboard-data-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.section-title {
  font-size: 1.125rem;
  color: #34495e;
  margin-bottom: 15px;
}

.data-card {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  padding: 15px;
  height: 100%;
}

/* Recent attendance section */
.mini-table {
  width: 100%;
  border-collapse: collapse;
}

.mini-table th {
  text-align: left;
  padding: 10px;
  font-size: 0.75rem;
  color: #7f8c8d;
  border-bottom: 1px solid #f1f1f1;
}

.mini-table td {
  padding: 10px;
  font-size: 0.875rem;
  color: #2c3e50;
  border-bottom: 1px solid #f1f1f1;
}

.text-center {
  text-align: center;
}

.row-alternate {
  background-color: #f8f9fa;
}

/* Top employees section */
.top-employee {
  display: flex;
  align-items: center;
  padding: 10px;
  border-radius: 6px;
}

.employee-rank {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background-color: #f1f1f1;
  color: #7f8c8d;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  margin-right: 10px;
}

.employee-name {
  flex: 1;
  display: flex;
  align-items: center;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  margin-right: 10px;
}

.employee-details {
  display: flex;
  flex-direction: column;
}

.employee-fullname {
  font-size: 0.875rem;
  color: #2c3e50;
}

.employee-department {
  font-size: 0.75rem;
  color: #7f8c8d;
}

.employee-stats {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.employee-stat {
  font-size: 1rem;
  font-weight: 600;
  color: #2c3e50;
}

.employee-stat-label {
  font-size: 0.75rem;
  color: #7f8c8d;
}

/* Department performance section */
.department-section {
  margin-bottom: 30px;
}

.department-row {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  border-radius: 6px;
}

.department-name {
  min-width: 120px;
  font-size: 0.875rem;
  color: #2c3e50;
}

.department-bar-container {
  flex: 1;
  height: 8px;
  background-color: #f1f1f1;
  border-radius: 4px;
  margin: 0 15px;
  overflow: hidden;
}

.department-bar {
  height: 100%;
  border-radius: 4px;
  transition: width 0.5s;
}

.department-value {
  font-size: 0.875rem;
  color: #7f8c8d;
  min-width: 100px;
  text-align: right;
}

/* Empty states and loading */
.empty-message {
  padding: 20px;
  text-align: center;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px;
  color: #95a5a6;
}

.empty-icon {
  font-size: 2rem;
  margin-bottom: 10px;
}

.loading-spinner {
  width: 30px;
  height: 30px;
  border: 3px solid #f1f1f1;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Add Button */
.add-button-container {
  display: flex;
  justify-content: flex-end;
  margin-top: 20px;
}

.btn-add {
  display: flex;
  align-items: center;
  padding: 10px 20px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-add:hover {
  background-color: #2980b9;
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.add-icon {
  margin-right: 10px;
}

/* Modal styles */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background-color: white;
  border-radius: 10px;
  width: 90%;
  max-width: 600px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-header {
  padding: 15px 20px;
  border-bottom: 1px solid #f1f1f1;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  color: #2c3e50;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #95a5a6;
}

.modal-body {
  padding: 20px;
  max-height: 70vh;
  overflow-y: auto;
}

.modal-footer {
  padding: 15px 20px;
  border-top: 1px solid #f1f1f1;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Form styles */
.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #2c3e50;
}

.form-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  font-size: 0.875rem;
}

.date-range-picker {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-range-separator {
  color: #7f8c8d;
}

.report-format-options {
  display: flex;
  gap: 10px;
}

.format-option {
  flex: 1;
  padding: 10px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  text-align: center;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
  background-color: #f8f9fa;
}

.format-option:hover {
  background-color: #f1f1f1;
}

.format-option.selected {
  background-color: #3498db;
  color: white;
  border-color: #3498db;
}

.checkbox-options {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.checkbox-container {
  display: flex;
  align-items: center;
  cursor: pointer;
  font-size: 0.875rem;
  color: #2c3e50;
  position: relative;
  padding-left: 30px;
}

.checkbox-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.checkmark {
  position: absolute;
  top: 0;
  left: 0;
  height: 20px;
  width: 20px;
  background-color: #f1f1f1;
  border-radius: 4px;
  border: 1px solid #e0e0e0;
}

.checkbox-container:hover input ~ .checkmark {
  background-color: #e0e0e0;
}

.checkbox-container input:checked ~ .checkmark {
  background-color: #3498db;
  border-color: #3498db;
}

.checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
  display: block;
}

.checkbox-container .checkmark:after {
  left: 7px;
  top: 3px;
  width: 5px;
  height: 10px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

/* Buttons */
.btn-primary {
  padding: 10px 20px;
  background-color: #3498db;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-primary:hover {
  background-color: #2980b9;
}

.btn-secondary {
  padding: 10px 20px;
  background-color: #f1f1f1;
  color: #2c3e50;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background-color: #e0e0e0;
}

/* Alert */
.alert-floating {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 15px 20px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  z-index: 1100;
  max-width: 400px;
}

.alert-success {
  background-color: #2ecc71;
  color: white;
}

/* Animations */
.animation-fade {
  animation: fadeIn 0.5s ease-in-out;
}

.animation-slide-up {
  animation: slideUp 0.3s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(20px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}
</style>