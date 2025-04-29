<template>
    <div class="container mx-auto py-6 px-4">
      <!-- Background patterns -->
      <div class="background-pattern pattern-dots"></div>
      <div class="background-pattern pattern-lines"></div>
      
      <div class="card">
        <div class="card-header">
          <h1 class="text-xl font-bold text-primary">Daftar Hadir Kegiatan</h1>
        </div>
  
        <!-- Search and Filter Section -->
        <div class="search-filter-bar">
          <div class="search-input-wrapper">
            <i class="search-icon">🔍</i>
            <input
              type="text"
              class="search-input"
              placeholder="Cari nama atau kegiatan..."
              v-model="searchQuery"
            />
          </div>
          <div class="filter-wrapper">
            <select v-model="selectedKegiatan" class="filter-select">
              <option value="">Semua Kegiatan</option>
              <option v-for="kegiatan in kegiatanList" :key="kegiatan" :value="kegiatan">{{ kegiatan }}</option>
            </select>
          </div>
          <div class="filter-wrapper">
            <input
              type="date"
              class="filter-select"
              v-model="selectedDate"
              placeholder="Filter tanggal"
            />
          </div>
        </div>
  
        <!-- Attendance Table -->
        <div class="table-container animation-fade">
          <table class="data-table">
            <thead>
              <tr>
                <th class="text-center">No</th>
                <th>Tanggal Kegiatan</th>
                <th>Nama Kegiatan</th>
                <th>Nama Peserta</th>
                <th>Keterangan</th>
                <th class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(attendance, index) in filteredAttendances" :key="attendance.id" 
                  :class="{'row-alternate': index % 2 === 0}">
                <td class="text-center">{{ index + 1 }}</td>
                <td>{{ formatDate(attendance.tanggal_kegiatan) }}</td>
                <td>{{ attendance.nama_kegiatan }}</td>
                <td>
                  <div class="employee-name">
                    <div class="avatar" :style="{ backgroundColor: getAvatarColor(attendance.nama) }">
                      {{ getInitials(attendance.nama) }}
                    </div>
                    <span>{{ attendance.nama }}</span>
                  </div>
                </td>
                <td>{{ attendance.keterangan || '-' }}</td>
                <td class="action-buttons">
                  <button @click="viewAttendance(attendance)" class="btn-view">
                    Lihat
                  </button>
                  <button @click="editAttendance(attendance)" class="btn-edit">
                    Edit
                  </button>
                </td>
              </tr>
              <tr v-if="loading">
                <td colspan="6" class="empty-message">
                  <div class="empty-state">
                    <div class="loading-spinner"></div>
                    <p>Memuat data...</p>
                  </div>
                </td>
              </tr>
              <tr v-else-if="filteredAttendances.length === 0">
                <td colspan="6" class="empty-message">
                  <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <p>Tidak ada data kehadiran yang ditemukan</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination">
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
        
        <!-- Add Attendance Button -->
        <div class="add-button-container">
          <button @click="showAddModal = true" class="btn-add">
            <span class="add-icon">+</span> Tambah Kehadiran
          </button>
        </div>
      </div>
  
      <!-- Attendance Detail Modal -->
      <div v-if="showAttendanceModal" class="modal-backdrop">
        <div class="modal-container animation-slide-up">
          <div class="modal-header">
            <h3 class="modal-title">Detail Kehadiran</h3>
            <button @click="showAttendanceModal = false" class="modal-close">×</button>
          </div>
          <div class="modal-body">
            <div class="attendance-profile">
              <div class="profile-header">
                <div class="large-avatar" :style="{ backgroundColor: getAvatarColor(selectedAttendance.nama) }">
                  {{ getInitials(selectedAttendance.nama) }}
                </div>
                <div class="profile-name">
                  <h4>{{ selectedAttendance.nama }}</h4>
                  <p class="text-sm text-gray-600">{{ selectedAttendance.nama_kegiatan }}</p>
                </div>
              </div>
              <div class="profile-details">
                <div class="detail-row">
                  <div class="detail-label">Tanggal Kegiatan</div>
                  <div class="detail-value">{{ formatDate(selectedAttendance.tanggal_kegiatan) }}</div>
                </div>
                <div class="detail-row">
                  <div class="detail-label">Nama Kegiatan</div>
                  <div class="detail-value">{{ selectedAttendance.nama_kegiatan }}</div>
                </div>
                <div class="detail-row">
                  <div class="detail-label">Nama Peserta</div>
                  <div class="detail-value">{{ selectedAttendance.nama }}</div>
                </div>
                <div class="detail-row">
                  <div class="detail-label">Keterangan</div>
                  <div class="detail-value description">{{ selectedAttendance.keterangan || '-' }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showAttendanceModal = false" class="btn-secondary">Tutup</button>
            <button @click="editAttendance(selectedAttendance)" class="btn-primary">Edit</button>
          </div>
        </div>
      </div>
  
      <!-- Add/Edit Attendance Modal -->
      <div v-if="showAddModal || showEditModal" class="modal-backdrop">
        <div class="modal-container animation-slide-up">
          <div class="modal-header">
            <h3 class="modal-title">{{ showEditModal ? 'Edit Kehadiran' : 'Tambah Kehadiran Baru' }}</h3>
            <button @click="closeFormModal" class="modal-close">×</button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="submitForm" class="attendance-form">
              <div class="form-group">
                <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                <input
                  type="date"
                  id="tanggal_kegiatan"
                  v-model="formData.tanggal_kegiatan"
                  class="form-input"
                  required
                />
              </div>
              
              <div class="form-group">
                <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                <input
                  type="text"
                  id="nama_kegiatan"
                  v-model="formData.nama_kegiatan"
                  class="form-input"
                  placeholder="Masukkan nama kegiatan"
                  required
                  list="kegiatan-names"
                />
                <datalist id="kegiatan-names">
                  <option v-for="(kegiatan, index) in kegiatanList" :key="index" :value="kegiatan"></option>
                </datalist>
              </div>
              
              <div class="form-group">
                <label for="nama" class="form-label">Nama Peserta</label>
                <input
                  type="text"
                  id="nama"
                  v-model="formData.nama"
                  class="form-input"
                  placeholder="Masukkan nama peserta"
                  required
                  list="participant-names"
                />
                <datalist id="participant-names">
                  <option v-for="(participant, index) in participantsList" :key="index" :value="participant"></option>
                </datalist>
              </div>
              
              <div class="form-group">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea
                  id="keterangan"
                  v-model="formData.keterangan"
                  class="form-input"
                  placeholder="Tambahkan keterangan jika diperlukan"
                  rows="3"
                ></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button @click="closeFormModal" class="btn-secondary">Batal</button>
            <button @click="closeFormModal" class="btn-secondary">Simpan</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import { format, parseISO } from 'date-fns';
  import { id } from 'date-fns/locale';
  
  export default {
    name: 'AttendanceView',
    data() {
      return {
        attendances: [
          {
            "id": 1,
            "tanggal_kegiatan": "2024-09-05",
            "nama_kegiatan": "PEMANFAATAN SERTIFIKAT ELEKTRONIK UNTUK TANDA TANGAN ELEKTRONIK",
            "nama": "ROBET TP SIAGIAN S.STP., M.Si",
            "keterangan": "Pembina Utama Muda"
          },
          {
            "id": 2,
            "tanggal_kegiatan": "2024-09-05",
            "nama_kegiatan": "PEMANFAATAN SERTIFIKAT ELEKTRONIK UNTUK TANDA TANGAN ELEKTRONIK",
            "nama": "SRI WAHYUNI S.E., M.Si",
            "keterangan": "Pembina Utama Muda"
          },
          {
            "id": 3,
            "tanggal_kegiatan": "2024-09-05",
            "nama_kegiatan": "PEMANFAATAN SERTIFIKAT ELEKTRONIK UNTUK TANDA TANGAN ELEKTRONIK",
            "nama": "SRI DEVINA SEMBIRING, S.Sos., M.Si",
            "keterangan": "Perencana Ahli Muda"
          },
          {
            "id": 4,
            "tanggal_kegiatan": "2024-09-10",
            "nama_kegiatan": "SOSIALISASI APLIKASI PENILAIAN KINERJA",
            "nama": "AHMAD FADHLI, S.Kom",
            "keterangan": "Pranata Komputer Ahli Muda"
          },
          {
            "id": 5,
            "tanggal_kegiatan": "2024-09-10",
            "nama_kegiatan": "SOSIALISASI APLIKASI PENILAIAN KINERJA",
            "nama": "DIAN PERMATASARI, S.T",
            "keterangan": "Analis Sistem Informasi"
          },
          {
            "id": 6,
            "tanggal_kegiatan": "2024-09-12",
            "nama_kegiatan": "WORKSHOP KEAMANAN SIBER",
            "nama": "BUDI SANTOSO, M.Kom",
            "keterangan": "Pranata Komputer Ahli Madya"
          },
          {
            "id": 7,
            "tanggal_kegiatan": "2024-09-12",
            "nama_kegiatan": "WORKSHOP KEAMANAN SIBER",
            "nama": "ROBET TP SIAGIAN S.STP., M.Si",
            "keterangan": "Pembina Utama Muda"
          },
          {
            "id": 8,
            "tanggal_kegiatan": "2024-09-15",
            "nama_kegiatan": "PELATIHAN MANAJEMEN DOKUMEN ELEKTRONIK",
            "nama": "SRI DEVINA SEMBIRING, S.Sos., M.Si",
            "keterangan": "Perencana Ahli Muda"
          },
          {
            "id": 9,
            "tanggal_kegiatan": "2024-09-15",
            "nama_kegiatan": "PELATIHAN MANAJEMEN DOKUMEN ELEKTRONIK",
            "nama": "DEWI ANGGRAINI, S.IP",
            "keterangan": "Arsiparis Ahli Pertama"
          },
          {
            "id": 10,
            "tanggal_kegiatan": "2024-09-20",
            "nama_kegiatan": "BIMTEK PENGGUNAAN APLIKASI PERKANTORAN",
            "nama": "RIZKI ADITYA, S.Kom",
            "keterangan": "Pranata Komputer Ahli Pertama"
          },
          {
            "id": 11,
            "tanggal_kegiatan": "2024-09-20",
            "nama_kegiatan": "BIMTEK PENGGUNAAN APLIKASI PERKANTORAN",
            "nama": "YULIA SAFITRI, S.E",
            "keterangan": "Analis Kepegawaian Pertama"
          },
          {
            "id": 12,
            "tanggal_kegiatan": "2024-09-22",
            "nama_kegiatan": "DISKUSI PENGEMBANGAN SMART CITY",
            "nama": "HENDRA WIJAYA, S.T., M.T",
            "keterangan": "Perencana Ahli Madya"
          }
        ],
        participantsList: [
          "ROBET TP SIAGIAN S.STP., M.Si",
          "SRI WAHYUNI S.E., M.Si",
          "SRI DEVINA SEMBIRING, S.Sos., M.Si",
          "AHMAD FADHLI, S.Kom",
          "DIAN PERMATASARI, S.T",
          "BUDI SANTOSO, M.Kom",
          "DEWI ANGGRAINI, S.IP",
          "RIZKI ADITYA, S.Kom",
          "YULIA SAFITRI, S.E",
          "HENDRA WIJAYA, S.T., M.T"
        ],
        searchQuery: '',
        selectedKegiatan: '',
        selectedDate: '',
        pagination: {
          currentPage: 1,
          itemsPerPage: 8,
          totalPages: 1
        },
        showAttendanceModal: false,
        showAddModal: false,
        showEditModal: false,
        selectedAttendance: {},
        formData: {
          id: null,
          tanggal_kegiatan: '',
          nama_kegiatan: '',
          nama: '',
          keterangan: ''
        },
        loading: false, // Set to false since we're using static data
        error: null
      };
    },
    created() {
      // No need to fetch data since we have static data
      // this.fetchAttendances();
      // this.fetchParticipants();
  
      // Set default date to today
      const today = new Date();
      this.formData.tanggal_kegiatan = format(today, 'yyyy-MM-dd');
      
      // Calculate pagination
      this.pagination.totalPages = Math.ceil(this.attendances.length / this.pagination.itemsPerPage);
    },
    computed: {
      kegiatanList() {
        return [...new Set(this.attendances.map(att => att.nama_kegiatan))];
      },
      filteredAttendances() {
        let filtered = [...this.attendances];
        
        // Apply search filter
        if (this.searchQuery) {
          const query = this.searchQuery.toLowerCase();
          filtered = filtered.filter(att => 
            att.nama.toLowerCase().includes(query) || 
            att.nama_kegiatan.toLowerCase().includes(query)
          );
        }
        
        // Apply kegiatan filter
        if (this.selectedKegiatan) {
          filtered = filtered.filter(att => att.nama_kegiatan === this.selectedKegiatan);
        }
        
        // Apply date filter
        if (this.selectedDate) {
          const selectedDateStr = this.selectedDate;
          filtered = filtered.filter(att => 
            format(parseISO(att.tanggal_kegiatan), 'yyyy-MM-dd') === selectedDateStr
          );
        }
        
        // Calculate pagination
        this.pagination.totalPages = Math.ceil(filtered.length / this.pagination.itemsPerPage);
        
        // Apply pagination
        const start = (this.pagination.currentPage - 1) * this.pagination.itemsPerPage;
        const end = start + this.pagination.itemsPerPage;
        
        return filtered.slice(start, end);
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
      async fetchAttendances() {
        try {
          this.loading = true;
          // In a real application, this would call an API
          // const response = await axios.get('/api/attendances');
          // this.attendances = response.data;
          this.loading = false;
        } catch (error) {
          console.error('Error fetching attendances:', error);
          this.error = 'Gagal memuat data kehadiran. Silakan coba lagi nanti.';
          this.loading = false;
        }
      },
      async fetchParticipants() {
        try {
          // In a real application, this would call an API
          // const response = await axios.get('/api/participants');
          // this.participantsList = response.data.map(p => p.nama);
        } catch (error) {
          console.error('Error fetching participants:', error);
          // If API fails, extract unique participants from attendances
          this.participantsList = [...new Set(this.attendances.map(att => att.nama))];
        }
      },
      formatDate(date) {
        if (!date) return '-';
        return format(parseISO(date), 'dd MMMM yyyy', { locale: id });
      },
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
      },
      viewAttendance(attendance) {
        this.selectedAttendance = { ...attendance };
        this.showAttendanceModal = true;
      },
      editAttendance(attendance) {
        this.formData = {
          id: attendance.id,
          tanggal_kegiatan: format(parseISO(attendance.tanggal_kegiatan), 'yyyy-MM-dd'),
          nama_kegiatan: attendance.nama_kegiatan,
          nama: attendance.nama,
          keterangan: attendance.keterangan
        };
        this.showEditModal = true;
      },
      closeFormModal() {
        this.showAddModal = false;
        this.showEditModal = false;
        this.resetForm();
      },
      resetForm() {
        const today = new Date();
        this.formData = {
          id: null,
          tanggal_kegiatan: format(today, 'yyyy-MM-dd'),
          nama_kegiatan: '',
          nama: '',
          keterangan: ''
        };
      },
      async submitForm() {
        try {
          if (this.showEditModal) {
            // Update existing attendance
            // In a real application, this would call an API
            // await axios.put(`/api/attendances/${this.formData.id}`, this.formData);
            
            // Find and update the item in the local array
            const index = this.attendances.findIndex(a => a.id === this.formData.id);
            if (index !== -1) {
              this.attendances[index] = { ...this.attendances[index], ...this.formData };
            }
          } else {
            // Create new attendance
            // In a real application, this would call an API
            // const response = await axios.post('/api/attendances', this.formData);
            
            // For demo purposes, create a new attendance with a generated ID
            const newAttendance = {
              ...this.formData,
              id: Math.max(...this.attendances.map(a => a.id)) + 1,
            };
            this.attendances.push(newAttendance);
          }
          
          // Close modal and reset form
          this.closeFormModal();
          // Show success message (could implement toast notification here)
        } catch (error) {
          console.error('Error submitting form:', error);
          this.error = 'Gagal menyimpan data. Silakan coba lagi nanti.';
        }
      },
      getInitials(name) {
        return name
          .split(' ')
          .map(word => word[0])
          .join('')
          .substring(0, 2)
          .toUpperCase();
      },
      getAvatarColor(name) {
        const colors = [
          '#4361EE', '#3A0CA3', '#7209B7', '#F72585', '#4CC9F0',
          '#4895EF', '#560BAD', '#B5179E', '#F15BB5', '#00BBF9',
          '#00B4D8', '#0096C7', '#0077B6', '#023E8A', '#03045E'
        ];
        
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
          hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        
        return colors[Math.abs(hash) % colors.length];
      }
    }
  };
  </script>

<style scoped>
/* ===== COLOR VARIABLES ===== */
:root {
  --primary: #2563eb;
  --primary-dark: #1d4ed8;
  --primary-light: #dbeafe;
  --secondary: #0ea5e9;
  --secondary-dark: #0284c7;
  --success: #10b981;
  --success-light: #d1fae5;
  --danger: #ef4444;
  --danger-light: #fee2e2;
  --warning: #f59e0b;
  --warning-light: #fef3c7;
  --info: #6366f1;
  --info-light: #e0e7ff;
  --gray: #64748b;
  --gray-light: #f1f5f9;
  --gray-dark: #334155;
  --bg-light: #f8fafc;
  --white: #ffffff;
  --text-dark: #1e293b;
  --text-medium: #475569;
  --text-light: #94a3b8;
  --border-light: #e2e8f0;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --radius-sm: 0.25rem;
  --radius-md: 0.5rem;
  --radius-lg: 1rem;
}

/* ===== GENERAL STYLING ===== */
.container {
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

/* Background pattern styling */
.background-pattern {
  position: fixed;
  pointer-events: none;
  z-index: 0;
}

.pattern-dots {
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: radial-gradient(#5079a1 1px, transparent 1px), radial-gradient(#5079a1 1px, transparent 1px);
  background-size: 30px 30px;
  background-position: 0 0, 15px 15px;
  opacity: 0.03;
}

.pattern-lines {
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background-image: linear-gradient(135deg, #001a33 10%, transparent 10%, transparent 50%, #001a33 50%, #001a33 60%, transparent 60%, transparent 100%);
  background-size: 40px 40px;
  opacity: 0.02;
}

.card {
  background-color: rgba(255, 255, 255, 0.9);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-md);
  border: 1px solid var(--border-light);
  overflow: hidden;
  transition: all 0.3s ease;
  backdrop-filter: blur(5px);
  position: relative;
  z-index: 2;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, rgba(80, 121, 161, 0.05) 0%, rgba(0, 26, 51, 0.05) 100%);
  z-index: -1;
}

.card:hover {
  box-shadow: var(--shadow-lg);
}

.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border-light);
  background: linear-gradient(90deg, rgba(248, 250, 252, 0.8) 0%, rgba(226, 232, 240, 0.8) 100%);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.card-header::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 3px;
  background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
}

.text-primary {
  color: var(--primary);
}

.text-center {
  text-align: center;
}

.font-mono {
  font-family: 'Courier New', monospace;
}

/* ===== SEARCH AND FILTER ===== */
.search-filter-bar {
  padding: 1.25rem 1.5rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  border-bottom: 1px solid var(--border-light);
  background: linear-gradient(180deg, rgba(255, 255, 255, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
  flex-wrap: wrap;
}

.search-input-wrapper {
  flex: 1;
  position: relative;
  min-width: 240px;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
  font-style: normal;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  background-color: var(--white);
  color: var(--text-dark);
  transition: all 0.3s ease;
  box-shadow: var(--shadow-sm);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.filter-wrapper {
  flex: 0 0 auto;
  min-width: 180px;
}

.filter-select {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  background-color: var(--white);
  color: var(--text-dark);
  transition: all 0.3s ease;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2364748b'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd' /%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.5em;
  box-shadow: var(--shadow-sm);
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

/* ===== TABLE STYLING ===== */
.table-container {
  overflow-x: auto;
  background-color: var(--white);
  background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%235079a1' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.data-table thead tr {
  background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
}

.data-table th {
  padding: 1rem;
  font-weight: 600;
  color: var(--text-medium);
  border-bottom: 2px solid var(--border-light);
  white-space: nowrap;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.05em;
  position: sticky;
  top: 0;
  background: linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
  z-index: 10;
  box-shadow: 0 1px 0 0 var(--border-light);
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-light);
  color: var(--text-dark);
}

.data-table tbody tr {
  transition: all 0.2s ease;
  background-color: rgba(255, 255, 255, 0.7);
}

.data-table tbody tr:hover {
  background-color: rgba(219, 234, 254, 0.3);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
}

.row-alternate {
  background-color: rgba(241, 245, 249, 0.7) !important;
}

.empty-message {
  padding: 3rem 0;
  text-align: center;
  color: var(--text-medium);
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.empty-icon {
  font-size: 2rem;
  margin-bottom: 1rem;
  opacity: 0.7;
}

.loading-spinner {
  width: 2.5rem;
  height: 2.5rem;
  border: 3px solid var(--primary-light);
  border-radius: 50%;
  border-top-color: var(--primary);
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* ===== EMPLOYEE NAME WITH AVATAR ===== */
.employee-name {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  flex-shrink: 0;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.8);
}

/* ===== STATUS BADGES ===== */
.status-badge {
  padding: 0.35rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  display: inline-block;
  line-height: 1;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.status-active {
  background-color: var(--success-light);
  color: var(--success);
}

.status-inactive {
  background-color: var(--danger-light);
  color: var(--danger);
}

.badge-primary {
  background-color: var(--primary-light);
  color: var(--primary-dark);
}

.badge-info {
  background-color: var(--info-light);
  color: var(--info);
}

.badge-success {
  background-color: var(--success-light);
  color: var(--success);
}

.badge-warning {
  background-color: var(--warning-light);
  color: var(--warning);
}

.badge-danger {
  background-color: var(--danger-light);
  color: var(--danger);
}

.badge-gray {
  background-color: var(--gray-light);
  color: var(--gray);
}

/* ===== ACTION BUTTONS ===== */
.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-view, .btn-edit {
  padding: 0.4rem 0.75rem;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-view {
  background-color: var(--primary-light);
  color: var(--primary);
}

.btn-view:hover {
  background-color: var(--primary);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn-edit {
  background-color: var(--gray-light);
  color: var(--gray-dark);
}

.btn-edit:hover {
  background-color: var(--gray-dark);
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(51, 65, 85, 0.2);
}

/* ===== PAGINATION ===== */
.pagination {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid var(--border-light);
  background: linear-gradient(180deg, rgba(241, 245, 249, 0.9) 0%, rgba(248, 250, 252, 0.9) 100%);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-info {
  color: var(--text-medium);
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  gap: 0.25rem;
  align-items: center;
}

.pagination-numbers {
  display: flex;
  gap: 0.25rem;
  margin: 0 0.5rem;
}

.pagination-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 2rem;
  height: 2rem;
  padding: 0 0.5rem;
  border-radius: var(--radius-sm);
  background-color: var(--white);
  color: var(--text-medium);
  border: 1px solid var(--border-light);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(.pagination-btn-disabled) {
  background-color: var(--primary-light);
  color: var(--primary);
  border-color: var(--primary-light);
}

.pagination-btn-active {
  background-color: var(--primary);
  color: white;
  border-color: var(--primary);
}

.pagination-btn-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ===== ADD BUTTON ===== */
.add-button-container {
  padding: 1.5rem;
  display: flex;
  justify-content: flex-end;
}

.btn-add {
  padding: 0.75rem 1.5rem;
  border-radius: var(--radius-md);
  background-color: var(--primary);
  color: white;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn-add:hover {
  background-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
}

.add-icon {
  font-size: 1.25rem;
  line-height: 1;
}

/* ===== MODAL STYLING ===== */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal-container {
  width: 90%;
  max-width: 500px;
  background-color: white;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  overflow: hidden;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.modal-header {
  padding: 1.25rem;
  border-bottom: 1px solid var(--border-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-dark);
  margin: 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: var(--text-medium);
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.modal-close:hover {
  color: var(--danger);
  background-color: var(--danger-light);
}

.modal-body {
  padding: 1.5rem;
  overflow-y: auto;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border-light);
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  background-color: var(--gray-light);
}

.btn-primary, .btn-secondary {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-primary {
  background-color: var(--primary);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.btn-secondary {
  background-color: var(--gray-light);
  color: var(--gray-dark);
  border: 1px solid var(--border-light);
}

.btn-secondary:hover {
  background-color: var(--white);
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(100, 116, 139, 0.2);
}

/* ===== ATTENDANCE PROFILE ===== */
.attendance-profile {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-light);
}

.large-avatar {
  width: 4rem;
  height: 4rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1.25rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 3px solid rgba(255, 255, 255, 0.8);
}

.profile-name {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.profile-name h4 {
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0;
  color: var(--text-dark);
}

.profile-details {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-row {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
}

.detail-label {
  flex: 0 0 30%;
  color: var(--text-medium);
  font-weight: 500;
  font-size: 0.875rem;
}

.detail-value {
  flex: 1;
  color: var(--text-dark);
}

.description {
  line-height: 1.5;
}

/* ===== FORM STYLING ===== */
.attendance-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-medium);
}

.form-input {
  padding: 0.75rem 1rem;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  font-size: 0.875rem;
  color: var(--text-dark);
  background-color: var(--white);
  transition: all 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

textarea.form-input {
  resize: vertical;
  min-height: 100px;
}

/* ===== ANIMATIONS ===== */
.animation-fade {
  animation: fadeIn 0.5s ease-in-out;
}

.animation-slide-up {
  animation: slideUp 0.3s ease-out;
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

/* ===== RESPONSIVE STYLING ===== */
@media (max-width: 768px) {
  .search-filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-input-wrapper {
    width: 100%;
  }
  
  .filter-wrapper {
    width: 100%;
  }
  
  .detail-row {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .detail-label {
    flex: 0 0 100%;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .pagination {
    flex-direction: column;
    align-items: center;
  }
}

/* Mobile optimizations */
@media (max-width: 480px) {
  .data-table {
    font-size: 0.875rem;
  }
  
  .data-table td, .data-table th {
    padding: 0.75rem 0.5rem;
  }
  
  .employee-name {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .modal-container {
    width: 95%;
  }
}
</style>