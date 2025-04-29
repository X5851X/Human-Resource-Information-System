<template>
  <div class="container mx-auto py-6 px-4">
    <!-- Background patterns -->
    <div class="background-pattern pattern-dots"></div>
    <div class="background-pattern pattern-lines"></div>
    
    <div class="card">
      <div class="card-header">
        <h1 class="text-xl font-bold text-primary">Laporan Kehadiran</h1>
        <div class="flex gap-4">
          <button @click="exportToExcel" class="btn-secondary">
            <span class="mr-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
            </span>
            Export Excel
          </button>
          <button @click="printReport" class="btn-primary">
            <span class="mr-2">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
              </svg>
            </span>
            Print Laporan
          </button>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="filter-section">
        <div class="filter-grid">
          <div class="filter-item">
            <label class="filter-label">Jenis Laporan</label>
            <select v-model="reportType" class="filter-select">
              <option value="daily">Laporan Harian</option>
              <option value="monthly">Laporan Bulanan</option>
              <option value="department">Laporan Per Departemen</option>
              <option value="employee">Laporan Per Pegawai</option>
            </select>
          </div>
          
          <div class="filter-item" v-if="reportType === 'daily'">
            <label class="filter-label">Pilih Tanggal</label>
            <input type="date" v-model="selectedDate" class="filter-input">
          </div>
          
          <div class="filter-item" v-if="reportType === 'monthly' || reportType === 'department' || reportType === 'employee'">
            <label class="filter-label">Pilih Bulan</label>
            <select v-model="selectedMonth" class="filter-select">
              <option v-for="(month, index) in months" :key="index" :value="index + 1">{{ month }}</option>
            </select>
          </div>
          
          <div class="filter-item" v-if="reportType === 'monthly' || reportType === 'department' || reportType === 'employee'">
            <label class="filter-label">Pilih Tahun</label>
            <select v-model="selectedYear" class="filter-select">
              <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
            </select>
          </div>
          
          <div class="filter-item" v-if="reportType === 'department'">
            <label class="filter-label">Pilih Departemen</label>
            <select v-model="selectedDepartment" class="filter-select">
              <option value="">Semua Departemen</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
            </select>
          </div>
          
          <div class="filter-item" v-if="reportType === 'employee'">
            <label class="filter-label">Pilih Pegawai</label>
            <select v-model="selectedEmployee" class="filter-select">
              <option value="">Semua Pegawai</option>
              <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
            </select>
          </div>
          
          <div class="filter-action">
            <button @click="generateReport" class="btn-generate">Generate Laporan</button>
          </div>
        </div>
      </div>

      <!-- Report Summary -->
      <div class="summary-container" v-if="reportGenerated">
        <div class="summary-header">
          <h2 class="summary-title">Ringkasan {{ getReportTypeLabel }}</h2>
          <p class="summary-period">{{ reportPeriodLabel }}</p>
        </div>
        
        <div class="summary-cards">
          <div class="summary-card">
            <div class="summary-icon summary-present">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
              </svg>
            </div>
            <div class="summary-info">
              <h3 class="summary-info-title">Hadir</h3>
              <p class="summary-value">{{ summaryData.present }}</p>
              <p class="summary-percentage">{{ calculatePercentage(summaryData.present) }}%</p>
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
              <h3 class="summary-info-title">Terlambat</h3>
              <p class="summary-value">{{ summaryData.late }}</p>
              <p class="summary-percentage">{{ calculatePercentage(summaryData.late) }}%</p>
            </div>
          </div>
          
          <div class="summary-card">
            <div class="summary-icon summary-absent">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
              </svg>
            </div>
            <div class="summary-info">
              <h3 class="summary-info-title">Tidak Hadir</h3>
              <p class="summary-value">{{ summaryData.absent }}</p>
              <p class="summary-percentage">{{ calculatePercentage(summaryData.absent) }}%</p>
            </div>
          </div>
          
          <div class="summary-card">
            <div class="summary-icon summary-permit">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
            </div>
            <div class="summary-info">
              <h3 class="summary-info-title">Izin</h3>
              <p class="summary-value">{{ summaryData.permit }}</p>
              <p class="summary-percentage">{{ calculatePercentage(summaryData.permit) }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Chart -->
      <div class="chart-container" v-if="reportGenerated && reportType !== 'daily'">
        <div class="chart-header">
          <h3 class="chart-title">Grafik Kehadiran</h3>
          <div class="chart-toggle">
            <button @click="chartType = 'bar'" class="chart-toggle-btn" :class="{ 'active': chartType === 'bar' }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="12" width="6" height="8"></rect>
                <rect x="9" y="8" width="6" height="12"></rect>
                <rect x="15" y="4" width="6" height="16"></rect>
              </svg>
            </button>
            <button @click="chartType = 'line'" class="chart-toggle-btn" :class="{ 'active': chartType === 'line' }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
              </svg>
            </button>
            <button @click="chartType = 'pie'" class="chart-toggle-btn" :class="{ 'active': chartType === 'pie' }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
              </svg>
            </button>
          </div>
        </div>
        
        <div class="chart-wrapper">
          <!-- Chart placeholder - in actual implementation this would be a chart library -->
          <div class="chart-placeholder">
            <div class="chart-img" :class="chartType">
              <div class="chart-bars" v-if="chartType === 'bar'">
                <div class="chart-bar" style="height: 60%;" title="Hadir: 60%"></div>
                <div class="chart-bar" style="height: 15%;" title="Terlambat: 15%"></div>
                <div class="chart-bar" style="height: 10%;" title="Tidak Hadir: 10%"></div>
                <div class="chart-bar" style="height: 15%;" title="Izin: 15%"></div>
              </div>
              <div class="chart-lines" v-if="chartType === 'line'">
                <svg viewBox="0 0 300 150" xmlns="http://www.w3.org/2000/svg">
                  <polyline points="0,120 50,60 100,80 150,20 200,40 250,30 300,50" 
                          fill="none" stroke="#4361EE" stroke-width="3"/>
                </svg>
              </div>
              <div class="chart-pie" v-if="chartType === 'pie'">
                <div class="pie-segments">
                  <div class="pie-segment segment-present"></div>
                  <div class="pie-segment segment-late"></div>
                  <div class="pie-segment segment-absent"></div>
                  <div class="pie-segment segment-permit"></div>
                </div>
              </div>
            </div>
            <div class="chart-legend">
              <div class="legend-item">
                <div class="legend-color legend-present"></div>
                <div class="legend-text">Hadir</div>
              </div>
              <div class="legend-item">
                <div class="legend-color legend-late"></div>
                <div class="legend-text">Terlambat</div>
              </div>
              <div class="legend-item">
                <div class="legend-color legend-absent"></div>
                <div class="legend-text">Tidak Hadir</div>
              </div>
              <div class="legend-item">
                <div class="legend-color legend-permit"></div>
                <div class="legend-text">Izin</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Data Table -->
      <div class="table-container animation-fade" v-if="reportGenerated">
        <table class="data-table">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th>Nama</th>
              <th>Tanggal</th>
              <th>Jam Masuk</th>
              <th>Jam Keluar</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(record, index) in reportData" :key="index" 
                :class="{'row-alternate': index % 2 === 0}">
              <td class="text-center">{{ index + 1 }}</td>
              <td>
                <div class="employee-name">
                  <div class="avatar" :style="{ backgroundColor: getAvatarColor(record.nama) }">
                    {{ getInitials(record.nama) }}
                  </div>
                  <span>{{ record.nama }}</span>
                </div>
              </td>
              <td>{{ formatDate(record.tanggal) }}</td>
              <td>{{ record.jamMasuk || '-' }}</td>
              <td>{{ record.jamKeluar || '-' }}</td>
              <td>
                <span class="status-badge" :class="getStatusClass(record.status)">
                  {{ record.status }}
                </span>
              </td>
              <td>{{ record.keterangan || '-' }}</td>
            </tr>
            <tr v-if="reportData.length === 0">
              <td colspan="7" class="empty-message">
                <div class="empty-state">
                  <div class="empty-icon">📊</div>
                  <p>Tidak ada data untuk laporan ini</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="pagination" v-if="reportGenerated && reportData.length > 0">
        <div class="pagination-info">
          Halaman {{ pagination.currentPage }} dari {{ pagination.totalPages }}
        </div>
        <div class="pagination-controls">
          <button @click="prevPage" :disabled="pagination.currentPage === 1" 
                  class="pagination-btn" :class="{'pagination-btn-disabled': pagination.currentPage === 1}">
            ◀ Prev
          </button>
          <div class="pagination-numbers">
            <button v-for="page in displayedPages" :key="page" 
                    @click="goToPage(page)"
                    class="pagination-btn" 
                    :class="{'pagination-btn-active': pagination.currentPage === page}">
              {{ page }}
            </button>
          </div>
          <button @click="nextPage" :disabled="pagination.currentPage === pagination.totalPages" 
                  class="pagination-btn"
                  :class="{'pagination-btn-disabled': pagination.currentPage === pagination.totalPages}">
            Next ▶
          </button>
        </div>
      </div>
    </div>

    <!-- Alerts -->
    <transition name="fade">
      <div v-if="successMessage" class="alert-floating alert-success">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-4">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
          <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        {{ successMessage }}
      </div>
    </transition>

    <transition name="fade">
      <div v-if="errorMessage" class="alert-floating alert-error">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-4">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div class="flex-1">
          {{ errorMessage }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
export default {
  name: 'ReportsView',
  data() {
    return {
      // Report Settings
      reportType: 'monthly',
      selectedDate: new Date().toISOString().split('T')[0],
      selectedMonth: new Date().getMonth() + 1,
      selectedYear: new Date().getFullYear(),
      selectedDepartment: '',
      selectedEmployee: '',
      chartType: 'bar',
      reportGenerated: false,
      
      // Data
      months: [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ],
      years: [2024, 2025],
      departments: [
        { id: 1, name: 'IT' },
        { id: 2, name: 'HR' },
        { id: 3, name: 'Finance' },
        { id: 4, name: 'Marketing' },
        { id: 5, name: 'Operations' }
      ],
      employees: [
        { id: 1, name: 'Budi Santoso', department_id: 1 },
        { id: 2, name: 'Siti Nurhaliza', department_id: 2 },
        { id: 3, name: 'Rudi Hartono', department_id: 3 },
        { id: 4, name: 'Dewi Lestari', department_id: 4 },
        { id: 5, name: 'Ahmad Rizal', department_id: 5 }
      ],
      
      // Mock attendance data for demonstration (would come from API in real app)
      attendanceData: [
        { 
          id: 1, 
          nama: 'Budi Santoso', 
          tanggal: '2025-04-01', 
          jamMasuk: '08:00', 
          jamKeluar: '17:00', 
          status: 'Hadir', 
          keterangan: '',
          department_id: 1
        },
        { 
          id: 2, 
          nama: 'Siti Nurhaliza', 
          tanggal: '2025-04-01', 
          jamMasuk: '08:10', 
          jamKeluar: '17:05', 
          status: 'Hadir', 
          keterangan: '',
          department_id: 2
        },
        { 
          id: 3, 
          nama: 'Rudi Hartono', 
          tanggal: '2025-04-01', 
          jamMasuk: '08:35', 
          jamKeluar: '17:15', 
          status: 'Terlambat', 
          keterangan: 'Kemacetan',
          department_id: 3
        },
        { 
          id: 4, 
          nama: 'Dewi Lestari', 
          tanggal: '2025-04-01', 
          jamMasuk: null, 
          jamKeluar: null, 
          status: 'Tidak Hadir', 
          keterangan: 'Sakit',
          department_id: 4
        },
        { 
          id: 5, 
          nama: 'Ahmad Rizal', 
          tanggal: '2025-04-01', 
          jamMasuk: null, 
          jamKeluar: null, 
          status: 'Izin', 
          keterangan: 'Acara keluarga',
          department_id: 5
        },
        { 
          id: 6, 
          nama: 'Budi Santoso', 
          tanggal: '2025-04-02', 
          jamMasuk: '07:55', 
          jamKeluar: '17:05', 
          status: 'Hadir', 
          keterangan: '',
          department_id: 1
        },
        { 
          id: 7, 
          nama: 'Siti Nurhaliza', 
          tanggal: '2025-04-02', 
          jamMasuk: '08:03', 
          jamKeluar: '17:00', 
          status: 'Hadir', 
          keterangan: '',
          department_id: 2
        },
        { 
          id: 8, 
          nama: 'Rudi Hartono', 
          tanggal: '2025-04-02', 
          jamMasuk: '08:00', 
          jamKeluar: '17:10', 
          status: 'Hadir', 
          keterangan: '',
          department_id: 3
        },
        { 
          id: 9, 
          nama: 'Dewi Lestari', 
          tanggal: '2025-04-02', 
          jamMasuk: null, 
          jamKeluar: null, 
          status: 'Tidak Hadir', 
          keterangan: 'Sakit',
          department_id: 4
        },
        { 
          id: 10, 
          nama: 'Ahmad Rizal', 
          tanggal: '2025-04-02', 
          jamMasuk: '08:15', 
          jamKeluar: '17:00', 
          status: 'Terlambat', 
          keterangan: 'Kendala transportasi',
          department_id: 5
        },
        { 
          id: 11, 
          nama: 'Budi Santoso', 
          tanggal: '2025-04-03', 
          jamMasuk: '07:50', 
          jamKeluar: '17:00', 
          status: 'Hadir', 
          keterangan: '',
          department_id: 1
        },
        { 
          id: 12, 
          nama: 'Siti Nurhaliza', 
          tanggal: '2025-04-03', 
          jamMasuk: null, 
          jamKeluar: null, 
          status: 'Izin', 
          keterangan: 'Keperluan pribadi',
          department_id: 2
        },
      ],
      
      // Filtered report data
      reportData: [],
      
      // Pagination
      pagination: {
        currentPage: 1,
        itemsPerPage: 8,
        totalPages: 1
      },
      
      // Summary data
      summaryData: {
        present: 0,
        late: 0,
        absent: 0,
        permit: 0
      },
      
      // Messages
      successMessage: '',
      errorMessage: ''
    };
  },
  computed: {
    getReportTypeLabel() {
      const labels = {
        'daily': 'Laporan Harian',
        'monthly': 'Laporan Bulanan',
        'department': 'Laporan Per Departemen',
        'employee': 'Laporan Per Pegawai'
      };
      return labels[this.reportType] || 'Laporan';
    },
    reportPeriodLabel() {
      if (this.reportType === 'daily') {
        return this.formatDate(this.selectedDate);
      } else if (this.reportType === 'monthly') {
        return `${this.months[this.selectedMonth - 1]} ${this.selectedYear}`;
      } else if (this.reportType === 'department') {
        const deptName = this.selectedDepartment ? 
          this.departments.find(d => d.id === parseInt(this.selectedDepartment))?.name || 'Semua Departemen' : 
          'Semua Departemen';
        return `${this.months[this.selectedMonth - 1]} ${this.selectedYear} - ${deptName}`;
      } else if (this.reportType === 'employee') {
        const empName = this.selectedEmployee ? 
          this.employees.find(e => e.id === parseInt(this.selectedEmployee))?.name || 'Semua Pegawai' : 
          'Semua Pegawai';
        return `${this.months[this.selectedMonth - 1]} ${this.selectedYear} - ${empName}`;
      }
      return '';
    },
    displayedPages() {
      const total = this.pagination.totalPages;
      const current = this.pagination.currentPage;
      
      if (total <= 5) {
        return Array.from({ length: total }, (_, i) => i + 1);
      }
      
      if (current <= 3) {
        return [1, 2, 3, 4, 5];
      }
      
      if (current >= total - 2) {
        return [total - 4, total - 3, total - 2, total - 1, total];
      }
      
      return [current - 2, current - 1, current, current + 1, current + 2];
    }
  },
  methods: {
    generateReport() {
      this.resetMessages();
      this.pagination.currentPage = 1;
      this.reportGenerated = false;
      
      try {
        // Filter attendance data based on report type
        let filteredData = [...this.attendanceData];
        
        if (this.reportType === 'daily') {
          filteredData = filteredData.filter(item => item.tanggal === this.selectedDate);
        } else {
          // For monthly, department, and employee reports, filter by month and year
          const month = parseInt(this.selectedMonth);
          const year = parseInt(this.selectedYear);
          
          filteredData = filteredData.filter(item => {
            const date = new Date(item.tanggal);
            return date.getMonth() + 1 === month && date.getFullYear() === year;
          });
          
          // Additional filters for department and employee reports
          if (this.reportType === 'department' && this.selectedDepartment) {
            filteredData = filteredData.filter(item => item.department_id === parseInt(this.selectedDepartment));
          } else if (this.reportType === 'employee' && this.selectedEmployee) {
            const employeeName = this.employees.find(e => e.id === parseInt(this.selectedEmployee))?.name;
            if (employeeName) {
              filteredData = filteredData.filter(item => item.nama === employeeName);
            }
          }
        }
        
        // Update reportData
        this.reportData = filteredData;
        
        // Calculate pagination
        this.pagination.totalPages = Math.ceil(this.reportData.length / this.pagination.itemsPerPage);
        
        // Calculate summary data
        this.calculateSummary();
        
        this.reportGenerated = true;
        this.successMessage = 'Laporan berhasil dibuat';
        setTimeout(() => {
          this.successMessage = '';
        }, 3000);
      } catch (error) {
        console.error('Error generating report:', error);
        this.errorMessage = 'Terjadi kesalahan saat membuat laporan';
        setTimeout(() => {
          this.errorMessage = '';
        }, 3000);
      }
    },
    
    calculateSummary() {
      this.summaryData = {
        present: this.reportData.filter(item => item.status === 'Hadir').length,
        late: this.reportData.filter(item => item.status === 'Terlambat').length,
        absent: this.reportData.filter(item => item.status === 'Tidak Hadir').length,
        permit: this.reportData.filter(item => item.status === 'Izin').length
      };
    },
    calculatePercentage(value) {
      const total = this.summaryData.present + this.summaryData.late + 
                    this.summaryData.absent + this.summaryData.permit;
      if (total === 0) return 0;
      return Math.round((value / total) * 100);
    },
    
    formatDate(dateString) {
      if (!dateString) return '-';
      
      const date = new Date(dateString);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      
      return `${day}/${month}/${year}`;
    },
    
    getStatusClass(status) {
      const statusClasses = {
        'Hadir': 'status-present',
        'Terlambat': 'status-late',
        'Tidak Hadir': 'status-absent',
        'Izin': 'status-permit'
      };
      
      return statusClasses[status] || '';
    },
    
    getInitials(name) {
      if (!name) return '';
      return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2);
    },
    
    getAvatarColor(name) {
      if (!name) return '#cccccc';
      
      // Simple hash function to generate color based on name
      const colorOptions = [
        '#4361EE', '#3A0CA3', '#7209B7', '#F72585', 
        '#4CC9F0', '#4895EF', '#560BAD', '#B5179E'
      ];
      
      let hash = 0;
      for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
      }
      
      const index = Math.abs(hash) % colorOptions.length;
      return colorOptions[index];
    },
    
    exportToExcel() {
      this.resetMessages();
      
      // In a real application, this would use a library to generate Excel files
      // For this demonstration, we'll just show a success message
      this.successMessage = 'Laporan berhasil diexport ke Excel';
      setTimeout(() => {
        this.successMessage = '';
      }, 3000);
    },
    
    printReport() {
      this.resetMessages();
      
      // In a real application, this would trigger the print dialog
      // For this demonstration, we'll just show a success message
      window.print();
      this.successMessage = 'Laporan dicetak';
      setTimeout(() => {
        this.successMessage = '';
      }, 3000);
    },
    
    resetMessages() {
      this.successMessage = '';
      this.errorMessage = '';
    },
    
    // Pagination methods
    prevPage() {
      if (this.pagination.currentPage > 1) {
        this.pagination.currentPage--;
      }
    },
    
    nextPage() {
      if (this.pagination.currentPage < this.pagination.totalPages) {
        this.pagination.currentPage++;
      }
    },
    
    goToPage(page) {
      this.pagination.currentPage = page;
    }
  },
  mounted() {
    // Pre-select the current date, month, and year
    const now = new Date();
    this.selectedDate = now.toISOString().split('T')[0];
    this.selectedMonth = now.getMonth() + 1;
    this.selectedYear = now.getFullYear();
  }
};
</script>

<style>
/* General Styles */
.container {
  max-width: 1200px;
  position: relative;
  overflow: hidden;
}

.background-pattern {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: -1;
  opacity: 0.05;
  pointer-events: none;
}

.pattern-dots {
  background-image: radial-gradient(#4361EE 1px, transparent 1px);
  background-size: 20px 20px;
}

.pattern-lines {
  background-image: linear-gradient(45deg, #4361EE 25%, transparent 25%),
                    linear-gradient(-45deg, #4361EE 25%, transparent 25%);
  background-size: 60px 60px;
  opacity: 0.02;
}

.card {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.card-header {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0f0f0;
}

.text-primary {
  color: #4361EE;
}

/* Button Styles */
.btn-primary, .btn-secondary {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.btn-primary {
  background-color: #4361EE;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #3A0CA3;
}

.btn-secondary {
  background-color: #f8f9fa;
  color: #4361EE;
  border: 1px solid #e9ecef;
}

.btn-secondary:hover {
  background-color: #e9ecef;
}

.btn-generate {
  background-color: #4361EE;
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-generate:hover {
  background-color: #3A0CA3;
  transform: translateY(-1px);
}

/* Filter Section */
.filter-section {
  padding: 1.5rem;
  background-color: #f8f9fa;
  border-bottom: 1px solid #f0f0f0;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.filter-item {
  display: flex;
  flex-direction: column;
}

.filter-label {
  font-size: 0.875rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #495057;
}

.filter-select, .filter-input {
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 0.875rem;
}

.filter-action {
  display: flex;
  align-items: flex-end;
}

/* Summary Section */
.summary-container {
  padding: 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.summary-header {
  margin-bottom: 1rem;
}

.summary-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
  margin-bottom: 0.25rem;
}

.summary-period {
  font-size: 0.875rem;
  color: #6c757d;
}

.summary-cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.summary-card {
  background-color: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  align-items: center;
  transition: transform 0.2s ease;
}

.summary-card:hover {
  transform: translateY(-3px);
}

.summary-icon {
  width: 48px;
  height: 48px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
}

.summary-present {
  background-color: rgba(67, 97, 238, 0.15);
  color: #4361EE;
}

.summary-late {
  background-color: rgba(255, 193, 7, 0.15);
  color: #FFC107;
}

.summary-absent {
  background-color: rgba(220, 53, 69, 0.15);
  color: #DC3545;
}

.summary-permit {
  background-color: rgba(108, 117, 125, 0.15);
  color: #6C757D;
}

.summary-info {
  flex: 1;
}

.summary-info-title {
  font-size: 0.875rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.summary-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #212529;
  margin-bottom: 0.25rem;
}

.summary-percentage {
  font-size: 0.875rem;
  color: #6c757d;
}

/* Chart Section */
.chart-container {
  padding: 1.5rem;
  border-bottom: 1px solid #f0f0f0;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.chart-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #212529;
}

.chart-toggle {
  display: flex;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  overflow: hidden;
}

.chart-toggle-btn {
  padding: 0.5rem;
  background-color: #f8f9fa;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-toggle-btn.active {
  background-color: #4361EE;
  color: white;
}

.chart-wrapper {
  height: 300px;
  margin: 1rem 0;
}

.chart-placeholder {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.chart-img {
  flex: 1;
  background-color: #f8f9fa;
  border-radius: 8px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 1rem;
  position: relative;
}

/* Chart visualization styles */
.chart-bars {
  display: flex;
  align-items: flex-end;
  height: 100%;
  width: 100%;
  justify-content: space-around;
}

.chart-bar {
  width: 60px;
  background-color: #4361EE;
  border-radius: 6px 6px 0 0;
  margin: 0 5px;
}

.chart-bar:nth-child(1) {
  background-color: #4361EE;
}

.chart-bar:nth-child(2) {
  background-color: #FFC107;
}

.chart-bar:nth-child(3) {
  background-color: #DC3545;
}

.chart-bar:nth-child(4) {
  background-color: #6C757D;
}

.chart-lines {
  width: 100%;
  height: 100%;
}

.chart-pie {
  width: 180px;
  height: 180px;
  position: relative;
}

.pie-segments {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  position: relative;
  overflow: hidden;
}

.pie-segment {
  position: absolute;
  width: 100%;
  height: 100%;
}

.segment-present {
  background-color: #4361EE;
  clip-path: polygon(50% 50%, 50% 0%, 100% 0%, 100% 100%, 50% 100%);
}

.segment-late {
  background-color: #FFC107;
  clip-path: polygon(50% 50%, 100% 100%, 50% 100%);
}

.segment-absent {
  background-color: #DC3545;
  clip-path: polygon(50% 50%, 50% 100%, 0% 100%, 0% 50%);
}

.segment-permit {
  background-color: #6C757D;
  clip-path: polygon(50% 50%, 0% 50%, 0% 0%, 50% 0%);
}

.chart-legend {
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 1rem;
}

.legend-item {
  display: flex;
  align-items: center;
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 3px;
  margin-right: 0.5rem;
}

.legend-present {
  background-color: #4361EE;
}

.legend-late {
  background-color: #FFC107;
}

.legend-absent {
  background-color: #DC3545;
}

.legend-permit {
  background-color: #6C757D;
}

.legend-text {
  font-size: 0.875rem;
  color: #6c757d;
}

/* Table Styles */
.table-container {
  padding: 1.5rem;
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 0.75rem;
  text-align: left;
  vertical-align: middle;
}

.data-table th {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
  white-space: nowrap;
}

.data-table tbody tr {
  border-bottom: 1px solid #f0f0f0;
}

.data-table tbody tr:hover {
  background-color: #f8f9fa;
}

.row-alternate {
  background-color: #fafafa;
}

.text-center {
  text-align: center;
}

.employee-name {
  display: flex;
  align-items: center;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.75rem;
  margin-right: 0.75rem;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
}

.status-present {
  background-color: rgba(67, 97, 238, 0.15);
  color: #4361EE;
}

.status-late {
  background-color: rgba(255, 193, 7, 0.15);
  color: #FFC107;
}

.status-absent {
  background-color: rgba(220, 53, 69, 0.15);
  color: #DC3545;
}

.status-permit {
  background-color: rgba(108, 117, 125, 0.15);
  color: #6C757D;
}

.empty-message {
  text-align: center;
  padding: 2rem;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.empty-icon {
  font-size: 2rem;
  margin-bottom: 1rem;
}

/* Pagination Styles */
.pagination {
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.pagination-info {
  font-size: 0.875rem;
  color: #6c757d;
}

.pagination-controls {
  display: flex;
  align-items: center;
}

.pagination-numbers {
  display: flex;
  margin: 0 0.5rem;
}

.pagination-btn {
  padding: 0.375rem 0.75rem;
  border: 1px solid #e9ecef;
  background-color: white;
  cursor: pointer;
  margin: 0 0.125rem;
  border-radius: 4px;
  font-size: 0.875rem;
  color: #495057;
}

.pagination-btn-active {
  background-color: #4361EE;
  color: white;
  border-color: #4361EE;
}

.pagination-btn-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Alert Styles */
.alert-floating {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 1rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  max-width: 450px;
  z-index: 100;
  animation: slideIn 0.3s ease-out;
}

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border-left: 4px solid #28a745;
}

.alert-error {
  background-color: #f8d7da;
  color: #721c24;
  border-left: 4px solid #dc3545;
}

/* Animations */
@keyframes slideIn {
  from {
    transform: translateY(100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity .3s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
}

.animation-fade {
  animation: fade 0.5s ease-in;
}

@keyframes fade {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .card-header div {
    margin-top: 1rem;
    width: 100%;
  }
  
  .card-header div button {
    width: 100%;
    justify-content: center;
    margin-bottom: 0.5rem;
  }
  
  .filter-grid {
    grid-template-columns: 1fr;
  }
  
  .summary-cards {
    grid-template-columns: 1fr;
  }
  
  .pagination {
    flex-direction: column;
  }
  
  .pagination-info {
    margin-bottom: 1rem;
  }
}

/* Print styles */
@media print {
  body {
    background-color: white;
  }
  
  .container {
    margin: 0;
    padding: 0;
    width: 100%;
  }
  
  .card {
    box-shadow: none;
    border: none;
  }
  
  .btn-primary, 
  .btn-secondary, 
  .filter-section, 
  .pagination, 
  .alert-floating {
    display: none !important;
  }
  
  .table-container {
    overflow-x: visible;
  }
}
</style>