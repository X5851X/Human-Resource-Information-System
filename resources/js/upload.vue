<template>
  <MainLayout>
    <div class="upload-container">
      <!-- Main Content -->
      <h1 class="page-title">Upload File Excel</h1>
      <p class="instructions">
        Silakan unggah file Excel berformat <strong>xlsx, xls, csv</strong> untuk memperbarui data.
      </p>

      <!-- Loading Indicator -->
      <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Sedang memproses...</p>
      </div>

      <!-- Alert Messages -->
      <div v-if="errors.length > 0" class="alert alert-danger">
        <ul>
          <li v-for="(error, index) in errors" :key="index">{{ error }}</li>
        </ul>
      </div>

      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>

      <!-- Upload Instructions -->
      <div class="upload-instructions">
        <h2 class="section-title">Tata Cara Upload:</h2>
        <ul class="instructions-list">
          <li>Pastikan format file sesuai dengan yang diperkenankan.</li>
          <li>File harus dalam format <strong>xlsx, xls, csv</strong> dan tidak boleh mengandung duplikasi data (case-sensitive).</li>
          <li>Jika ada data yang sudah ada namun berbeda, maka akan ditambahkan sebagai data baru.</li>
          <li>Baris pertama pada file harus berisi nama kolom yang sesuai.</li>
        </ul>
      </div>

      <!-- Employee Upload Section -->
      <div class="upload-section">
        <div class="section-header">
          <div class="section-number">1</div>
          <h3 class="section-name">Upload Kepegawaian</h3>
        </div>
        
        <div class="format-info">
          <p class="format-title">Format yang diperkenankan:</p>
          <div class="format-table">
            <span class="format-column">no</span>
            <span class="format-separator">|</span>
            <span class="format-column">nama</span>
            <span class="format-separator">|</span>
            <span class="format-column">jabatan</span>
            <span class="format-separator">|</span>
            <span class="format-column">nomor_induk_pegawai</span>
            <span class="format-separator">|</span>
            <span class="format-column">keterangan</span>
            <span class="format-separator">|</span>
            <span class="format-column">bidang</span>
          </div>
        </div>

        <form @submit.prevent="uploadFile('kepegawaian')" class="upload-form">
          <div class="file-input-container">
            <label for="employeeFile" class="file-label">Pilih File Excel:</label>
            <input 
              type="file" 
              id="employeeFile" 
              ref="employeeFileInput"
              accept=".xlsx,.xls,.csv" 
              class="file-input" 
              @change="handleEmployeeFileChange"
            />
          </div>
          <button type="submit" class="upload-button employee-upload" :disabled="isLoading || !employeeFile">
            <i class="fas fa-upload"></i> Upload Kepegawaian
          </button>
        </form>
      </div>

      <!-- Attendance Upload Section -->
      <div class="upload-section">
        <div class="section-header">
          <div class="section-number">2</div>
          <h3 class="section-name">Upload Daftar Hadir</h3>
        </div>
        
        <div class="format-info">
          <p class="format-title">Format yang diperkenankan:</p>
          <div class="format-table">
            <span class="format-column">no</span>
            <span class="format-separator">|</span>
            <span class="format-column">tanggal_kegiatan</span>
            <span class="format-separator">|</span>
            <span class="format-column">nama_kegiatan</span>
            <span class="format-separator">|</span>
            <span class="format-column">nama</span>
            <span class="format-separator">|</span>
            <span class="format-column">keterangan</span>
          </div>
        </div>

        <form @submit.prevent="uploadFile('daftar_hadir')" class="upload-form">
          <div class="file-input-container">
            <label for="attendanceFile" class="file-label">Pilih File Excel:</label>
            <input 
              type="file" 
              id="attendanceFile" 
              ref="attendanceFileInput"
              accept=".xlsx,.xls,.csv" 
              class="file-input" 
              @change="handleAttendanceFileChange"
            />
          </div>
          <button type="submit" class="upload-button attendance-upload" :disabled="isLoading || !attendanceFile">
            <i class="fas fa-upload"></i> Upload Daftar Hadir
          </button>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Upload',
  data() {
    return {
      employeeFile: null,
      attendanceFile: null,
      successMessage: '',
      errors: [],
      isLoading: false,
      currentYear: new Date().getFullYear()
    };
  },
  methods: {
    handleEmployeeFileChange(e) {
      const file = e.target.files[0];
      if (!file) {
        this.employeeFile = null;
        return;
      }

      const validTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.ms-excel', // xls
        'text/csv' // csv
      ];

      if (validTypes.includes(file.type) || file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv')) {
        this.employeeFile = file;
        this.errors = [];
      } else {
        this.errors = ['File harus berformat xlsx, xls, atau csv'];
        this.employeeFile = null;
        this.$refs.employeeFileInput.value = '';
      }
    },
    handleAttendanceFileChange(e) {
      const file = e.target.files[0];
      if (!file) {
        this.attendanceFile = null;
        return;
      }

      const validTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
        'application/vnd.ms-excel', // xls
        'text/csv' // csv
      ];

      if (validTypes.includes(file.type) || file.name.endsWith('.xlsx') || file.name.endsWith('.xls') || file.name.endsWith('.csv')) {
        this.attendanceFile = file;
        this.errors = [];
      } else {
        this.errors = ['File harus berformat xlsx, xls, atau csv'];
        this.attendanceFile = null;
        this.$refs.attendanceFileInput.value = '';
      }
    },
    async uploadFile(type) {
      this.errors = [];
      this.successMessage = '';
      this.isLoading = true;

      try {
        const formData = new FormData();
        
        if (type === 'kepegawaian') {
          if (!this.employeeFile) {
            this.errors.push('Silakan pilih file terlebih dahulu');
            this.isLoading = false;
            return;
          }
          formData.append('file', this.employeeFile);
        } else if (type === 'daftar_hadir') {
          if (!this.attendanceFile) {
            this.errors.push('Silakan pilih file terlebih dahulu');
            this.isLoading = false;
            return;
          }
          formData.append('file', this.attendanceFile);
        }

        formData.append('type', type);
        
        // Get CSRF token from meta tag or Laravel's js variable
        let token = '';
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
          token = metaTag.getAttribute('content');
        } else if (window.Laravel && window.Laravel.csrfToken) {
          token = window.Laravel.csrfToken;
        }

        const response = await axios.post('/upload', formData, {
          headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'multipart/form-data'
          }
        });

        console.log('Upload response:', response.data);

        // Check for success based on standard Laravel response or your custom response
        if (response.data.success || response.status === 200) {
          this.successMessage = response.data.success || response.data.message || 'File berhasil diunggah dan diproses.';
          
          // Reset file input
          if (type === 'kepegawaian') {
            this.$refs.employeeFileInput.value = '';
            this.employeeFile = null;
          } else {
            this.$refs.attendanceFileInput.value = '';
            this.attendanceFile = null;
          }

          // Add warnings if present
          if (response.data.warnings && response.data.warnings.length > 0) {
            this.errors = response.data.warnings;
          }
        } else {
          // Handle error
          this.errors.push(response.data.message || 'Gagal mengunggah file.');
        }
      } catch (error) {
        console.error('Upload error:', error);
        
        // Handle different types of error responses from Laravel
        if (error.response) {
          console.log('Error response data:', error.response.data);
          
          // Handle Laravel validation errors
          if (error.response.data.errors) {
            // Convert object of arrays to flat array of messages
            this.errors = Object.values(error.response.data.errors).flat();
          } 
          // Handle error messages
          else if (error.response.data.message) {
            this.errors.push(error.response.data.message);
          } 
          // Handle error arrays
          else if (error.response.data.error) {
            if (Array.isArray(error.response.data.error)) {
              this.errors = error.response.data.error;
            } else {
              this.errors.push(error.response.data.error);
            }
          } 
          // Handle errorMessages array
          else if (error.response.data.errorMessages) {
            this.errors = error.response.data.errorMessages;
          }
          // Fallback error
          else {
            this.errors.push(`Error: ${error.response.status} - ${error.response.statusText}`);
          }
        } else if (error.request) {
          // Request was made but no response received
          this.errors.push('Tidak ada respon dari server. Periksa koneksi internet Anda.');
        } else {
          // Something happened in setting up the request
          this.errors.push('Terjadi kesalahan saat mengunggah file: ' + error.message);
        }

        // Reset file input on error
        if (type === 'kepegawaian') {
          this.$refs.employeeFileInput.value = '';
          this.employeeFile = null;
        } else {
          this.$refs.attendanceFileInput.value = '';
          this.attendanceFile = null;
        }
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>

<style scoped>
.upload-container {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
  position: relative;
}

.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(255, 255, 255, 0.8);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.spinner {
  border: 5px solid #f3f3f3;
  border-top: 5px solid #3498db;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.breadcrumb {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  font-size: 14px;
}

.home-link {
  color: #1e88e5;
  text-decoration: none;
}

.separator {
  margin: 0 8px;
  color: #666;
}

.current-page {
  color: #333;
  font-weight: 500;
}

.page-title {
  color: #1e88e5;
  font-size: 28px;
  margin-bottom: 10px;
  font-weight: 700;
}

.instructions {
  color: #333;
  margin-bottom: 24px;
}

.alert {
  padding: 12px 16px;
  border-radius: 4px;
  margin-bottom: 20px;
}

.alert-danger {
  background-color: #ffebee;
  color: #c62828;
  border: 1px solid #ef9a9a;
}

.alert-success {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.alert ul {
  margin: 0;
  padding-left: 20px;
}

.upload-instructions {
  margin-bottom: 30px;
}

.section-title {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 12px;
}

.instructions-list {
  padding-left: 20px;
  margin-bottom: 16px;
}

.upload-section {
  background-color: #f9f9f9;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  margin-bottom: 30px;
}

.section-header {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}

.section-number {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background-color: #3498db;
  color: white;
  border-radius: 50%;
  font-weight: 600;
  margin-right: 12px;
}

.section-name {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.format-info {
  margin-bottom: 20px;
}

.format-title {
  font-weight: 500;
  margin-bottom: 8px;
}

.format-table {
  display: flex;
  flex-wrap: wrap; 
  align-items: center;
  font-family: monospace;
  background-color: #f0f0f0;
  padding: 10px;
  border-radius: 4px;
  overflow-x: auto;
}

.format-column {
  padding: 0 8px;
  white-space: nowrap;
  font-weight: bold;
}

.format-separator {
  color: #666;
}

.upload-form {
  display: flex;
  flex-direction: column;
}

.file-input-container {
  margin-bottom: 16px;
}

.file-label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.file-input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background-color: white;
}

.upload-button {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.upload-button:disabled {
  background-color: #cccccc;
  cursor: not-allowed;
  opacity: 0.7;
}

.upload-button i {
  margin-right: 8px;
}

.employee-upload {
  background-color: #4caf50;
}

.employee-upload:hover:not(:disabled) {
  background-color: #388e3c;
}

.attendance-upload {
  background-color: #2196f3;
}

.attendance-upload:hover:not(:disabled) {
  background-color: #1976d2;
}

.footer {
  display: flex;
  justify-content: space-between;
  margin-top: 60px;
  padding-top: 20px;
  border-top: 1px solid #e0e0e0;
  color: #666;
  font-size: 14px;
}

.footer a {
  color: #1e88e5;
  text-decoration: none;
}

@media (max-width: 768px) {
  .format-table {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .format-separator {
    display: none;
  }
  
  .format-column {
    padding: 4px 0;
  }
  
  .footer {
    flex-direction: column;
    text-align: center;
  }
  
  .copyright {
    margin-bottom: 8px;
  }
}
</style>