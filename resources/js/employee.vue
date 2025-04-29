<template>
  <div class="container mx-auto py-6 px-4">
    <!-- Background patterns -->
    <div class="background-pattern pattern-dots"></div>
    <div class="background-pattern pattern-lines"></div>
    
    <div class="card">
      <div class="card-header">
        <h1 class="text-xl font-bold text-primary">Data Kepegawaian</h1>
      </div>

      <!-- Search and Filter Section -->
      <div class="search-filter-bar">
        <div class="search-input-wrapper">
          <i class="search-icon">🔍</i>
          <input
            type="text"
            class="search-input"
            placeholder="Cari nama pegawai..."
            v-model="searchQuery"
          />
        </div>
        <div class="filter-wrapper">
          <select v-model="selectedBidang" class="filter-select">
            <option value="">Semua Bidang</option>
            <option v-for="bidang in bidangList" :key="bidang" :value="bidang">{{ bidang }}</option>
          </select>
        </div>
      </div>

      <!-- Employee Table -->
      <div class="table-container animation-fade">
        <table class="data-table">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th>Nama</th>
              <th>Jabatan</th>
              <th>NIP</th>
              <th>Bidang</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(employee, index) in filteredEmployees" :key="employee.id" 
                :class="{'row-alternate': index % 2 === 0}">
              <td class="text-center">{{ index + 1 }}</td>
              <td>
                <div class="employee-name">
                  <div class="avatar" :style="{ backgroundColor: getAvatarColor(employee.nama) }">
                    {{ getInitials(employee.nama) }}
                  </div>
                  <span>{{ employee.nama }}</span>
                </div>
              </td>
              <td>{{ employee.jabatan }}</td>
              <td class="font-mono">{{ employee.nip }}</td>
              <td>
                <span class="badge" :class="getBidangClass(employee.bidang)">
                  {{ employee.bidang }}
                </span>
              </td>
              <td>
                <span class="status-badge" :class="employee.status === 'Aktif' ? 'status-active' : 'status-inactive'">
                  {{ employee.status }}
                </span>
              </td>
              <td class="action-buttons">
                <button @click="viewEmployee(employee)" class="btn-view">
                  Lihat
                </button>
                <button @click="editEmployee(employee)" class="btn-edit">
                  Edit
                </button>
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="7" class="empty-message">
                <div class="empty-state">
                  <div class="loading-spinner"></div>
                  <p>Memuat data...</p>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredEmployees.length === 0">
              <td colspan="7" class="empty-message">
                <div class="empty-state">
                  <div class="empty-icon">🔍</div>
                  <p>Tidak ada data pegawai yang ditemukan</p>
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
    </div>

    <!-- Employee Detail Modal -->
    <div v-if="showEmployeeModal" class="modal-backdrop">
      <div class="modal-container animation-slide-up">
        <div class="modal-header">
          <h3 class="modal-title">Detail Pegawai</h3>
          <button @click="showEmployeeModal = false" class="modal-close">×</button>
        </div>
        <div class="modal-body">
          <div class="employee-profile">
            <div class="profile-header">
              <div class="large-avatar" :style="{ backgroundColor: getAvatarColor(selectedEmployee.nama) }">
                {{ getInitials(selectedEmployee.nama) }}
              </div>
              <div class="profile-name">
                <h4>{{ selectedEmployee.nama }}</h4>
                <span class="badge" :class="getBidangClass(selectedEmployee.bidang)">
                  {{ selectedEmployee.bidang }}
                </span>
              </div>
            </div>
            <div class="profile-details">
              <div class="detail-row">
                <div class="detail-label">NIP</div>
                <div class="detail-value font-mono">{{ selectedEmployee.nip }}</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Jabatan</div>
                <div class="detail-value">{{ selectedEmployee.jabatan }}</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Bidang</div>
                <div class="detail-value">{{ selectedEmployee.bidang }}</div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Status</div>
                <div class="detail-value">
                  <span class="status-badge" :class="selectedEmployee.status === 'Aktif' ? 'status-active' : 'status-inactive'">
                    {{ selectedEmployee.status }}
                  </span>
                </div>
              </div>
              <div class="detail-row">
                <div class="detail-label">Keterangan</div>
                <div class="detail-value description">{{ selectedEmployee.keterangan || '-' }}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="showEmployeeModal = false" class="btn-secondary">Tutup</button>
          <button @click="openEditModal(selectedEmployee)" class="btn-primary">Edit</button>
        </div>       
      </div>
    </div>

            <!-- Add Employee Button -->
            <div class="add-button-container">
          <button @click="showAddModal = true" class="btn-add">
            <span class="add-icon">+</span> Tambah Pegawai
          </button>
        </div>
        
<!-- Modal Tambah Pegawai -->
<div id="addEmployeeModal" class="add-employee-modal">
  <div class="add-modal-container">
    <div class="add-modal-header">
      <h3 class="add-modal-title">
        <span class="add-modal-title-icon">+</span>
        Tambah Pegawai Baru
      </h3>
      <button class="add-modal-close" @click="closeAddModal">×</button>
    </div>
    
    <div class="add-modal-body">
      <form @submit.prevent="submitEmployeeForm" class="add-employee-form">
        <!-- Avatar Preview -->
        <div class="avatar-preview">
          <div class="avatar-preview-container" :style="{ backgroundColor: getAvatarColor(formData.nama) }">
            {{ getInitials(formData.nama) || '?' }}
          </div>
        </div>
        
        <!-- Data Diri -->
        <div class="form-section">
          <h4 class="form-section-title">Data Diri Pegawai</h4>
          
          <div class="field-list">
            <div class="form-group">
              <label for="nama" class="form-label label-required">Nama Lengkap</label>
              <input
                type="text"
                id="nama"
                v-model="formData.nama"
                class="form-input"
                placeholder="Masukkan nama lengkap"
                required
              />
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="nip" class="form-label label-required">NIP</label>
                <input
                  type="text"
                  id="nip"
                  v-model="formData.nip"
                  class="form-input"
                  placeholder="Contoh: 19770126 199703 1 003"
                  required
                />
              </div>
              
              <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <div class="input-icon-wrapper">
                  <span class="input-icon">✉️</span>
                  <input
                    type="email"
                    id="email"
                    v-model="formData.email"
                    class="form-input input-with-icon"
                    placeholder="nama@email.com"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Data Jabatan -->
        <div class="form-section">
          <h4 class="form-section-title">Informasi Jabatan</h4>
          
          <div class="field-list">
            <div class="form-group">
              <label for="jabatan" class="form-label label-required">Jabatan</label>
              <input
                type="text"
                id="jabatan"
                v-model="formData.jabatan"
                class="form-input"
                placeholder="Masukkan jabatan"
                required
              />
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="bidang" class="form-label label-required">Bidang</label>
                <select
                  id="bidang"
                  v-model="formData.bidang"
                  class="form-select"
                  required
                >
                  <option value="" disabled>Pilih Bidang</option>
                  <option value="SEKRETARIAT">SEKRETARIAT</option>
                  <option value="TIK">TIK</option>
                  <option value="PIP">PIP</option>
                  <option value="E-Government">E-Government</option>
                  <option value="STANDI">STANDI</option>
                </select>
              </div>
              
              <div class="form-group">
                <label for="status" class="form-label">Status</label>
                <select
                  id="status"
                  v-model="formData.status"
                  class="form-select"
                >
                  <option value="Aktif">Aktif</option>
                  <option value="Cuti">Cuti</option>
                  <option value="Tugas Belajar">Tugas Belajar</option>
                  <option value="Diperbantukan">Diperbantukan</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="golongan" class="form-label">Pangkat/Golongan</label>
              <select
                id="golongan"
                v-model="formData.golongan"
                class="form-select"
              >
                <option value="">- Pilih Pangkat/Golongan -</option>
                <option value="Pembina Utama, IV/e">Pembina Utama, IV/e</option>
                <option value="Pembina Utama Madya, IV/d">Pembina Utama Madya, IV/d</option>
                <option value="Pembina Utama Muda, IV/c">Pembina Utama Muda, IV/c</option>
                <option value="Pembina Tk. I, IV/b">Pembina Tk. I, IV/b</option>
                <option value="Pembina, IV/a">Pembina, IV/a</option>
                <option value="Penata Tk. I, III/d">Penata Tk. I, III/d</option>
                <option value="Penata, III/c">Penata, III/c</option>
                <option value="Penata Muda Tk. I, III/b">Penata Muda Tk. I, III/b</option>
                <option value="Penata Muda, III/a">Penata Muda, III/a</option>
              </select>
            </div>
          </div>
        </div>
        
        <!-- Keterangan -->
        <div class="form-group">
          <label for="keterangan" class="form-label">Keterangan Tambahan</label>
          <textarea
            id="keterangan"
            v-model="formData.keterangan"
            class="form-textarea"
            placeholder="Tambahkan keterangan lainnya (opsional)"
            rows="3"
          ></textarea>
          <div class="form-hint">Informasi tambahan yang perlu dicatat</div>
        </div>
        
        <!-- Status Akun -->
        <div class="form-group">
          <div class="toggle-container">
            <label class="toggle-switch">
              <input type="checkbox" v-model="formData.aktifkanAkun">
              <span class="toggle-slider"></span>
            </label>
            <span class="toggle-label">Aktifkan Akun Pengguna</span>
          </div>
          <div class="form-hint">Jika diaktifkan, pegawai akan mendapatkan akses ke sistem</div>
        </div>
      </form>
    </div>
    
    <div class="add-modal-footer">
      <button @click="closeAddModal" class="btn-cancel">Batal</button>
      <button @click="submitEmployeeForm" class="btn-save">
        <span class="btn-save-icon">💾</span>
        Simpan Data
      </button>
    </div>
  </div>
</div>

      <!-- Employee Detail Modal -->
      <div v-if="showEmployeeModal" class="modal-backdrop">
        <div class="modal-container animation-slide-up">
          <div class="modal-header">
        <h3 class="modal-title">Detail Pegawai</h3>
        <button @click="showEmployeeModal = false" class="modal-close">×</button>
          </div>
          <div class="modal-body">
        <div class="employee-profile">
          <div class="profile-header">
            <div class="large-avatar" :style="{ backgroundColor: getAvatarColor(selectedEmployee.nama) }">
          {{ getInitials(selectedEmployee.nama) }}
            </div>
            <div class="profile-name">
          <h4>{{ selectedEmployee.nama }}</h4>
          <span class="badge" :class="getBidangClass(selectedEmployee.bidang)">
            {{ selectedEmployee.bidang }}
          </span>
            </div>
          </div>
          <div class="profile-details">
            <div class="detail-row">
          <div class="detail-label">NIP</div>
          <div class="detail-value font-mono">{{ selectedEmployee.nip }}</div>
            </div>
            <div class="detail-row">
          <div class="detail-label">Jabatan</div>
          <div class="detail-value">{{ selectedEmployee.jabatan }}</div>
            </div>
            <div class="detail-row">
          <div class="detail-label">Bidang</div>
          <div class="detail-value">{{ selectedEmployee.bidang }}</div>
            </div>
            <div class="detail-row">
          <div class="detail-label">Status</div>
          <div class="detail-value">
            <span class="status-badge" :class="selectedEmployee.status === 'Aktif' ? 'status-active' : 'status-inactive'">
              {{ selectedEmployee.status }}
            </span>
          </div>
            </div>
            <div class="detail-row">
          <div class="detail-label">Keterangan</div>
          <div class="detail-value description">{{ selectedEmployee.keterangan || '-' }}</div>
            </div>
          </div>
        </div>
          </div>
          <div class="modal-footer">
        <button @click="showEmployeeModal = false" class="btn-secondary">Tutup</button>
        <button @click="openEditModal(selectedEmployee)" class="btn-primary">Edit</button>
          </div>
        </div>
      </div>
      <!-- Add/Edit Employee Modal -->
      <div v-if="showAddModal || showEditModal" class="modal-backdrop">
      <div class="modal-container animation-slide-up">
        <div class="modal-header">
        <h3 class="modal-title">{{ showEditModal ? 'Edit Pegawai' : 'Tambah Pegawai Baru' }}</h3>
        <button @click="closeFormModal" class="modal-close">×</button>
        </div>
        <div class="modal-body">
        <form @submit.prevent="submitForm" class="employee-form">
          <div class="form-group">
          <label for="nama" class="form-label">Nama Lengkap</label>
          <input
            type="text"
            id="nama"
            v-model="formData.nama"
            class="form-input"
            placeholder="Masukkan nama lengkap"
            required
          />
          </div>
          
          <div class="form-group">
          <label for="nip" class="form-label">NIP</label>
          <input
            type="text"
            id="nip"
            v-model="formData.nip"
            class="form-input"
            placeholder="Masukkan NIP"
            required
          />
          </div>
          
          <div class="form-group">
          <label for="jabatan" class="form-label">Jabatan</label>
          <input
            type="text"
            id="jabatan"
            v-model="formData.jabatan"
            class="form-input"
            placeholder="Masukkan jabatan"
            required
          />
          </div>
          
          <div class="form-group">
          <label for="bidang" class="form-label">Bidang</label>
          <select
            id="bidang"
            v-model="formData.bidang"
            class="form-input"
            required
          >
            <option value="">Pilih Bidang</option>
            <option v-for="bidang in bidangList" :key="bidang" :value="bidang">
            {{ bidang }}
            </option>
          </select>
          </div>

          <div class="form-group">
          <label for="status" class="form-label">Status</label>
          <select
            id="status"
            v-model="formData.status"
            class="form-input"
            required
          >
            <option value="Aktif">Aktif</option>
            <option value="Cuti">Cuti</option>
          </select>
          </div>
          
          <div class="form-group">
          <label for="keterangan" class="form-label">Keterangan</label>
          <textarea
            id="keterangan"
            v-model="formData.keterangan"
            class="form-input"
            placeholder="Tambahkan keterangan (pangkat/golongan)"
            rows="2"
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
export default {
  name: 'EmployeeView',
  data() {
    return {
      employees: [
        {
          id: 1,
          nama: "ROBET TP SIAGIAN S.STP., M.Si",
          nip: "19770126 199703 1 003",
          jabatan: "Kepala Dinas",
          bidang: "SEKRETARIAT",
          status: "Aktif",
          keterangan: "Pembina Utama Muda, IV/c"
        },
        {
          id: 2,
          nama: "SRI WAHYUNI S.E., M.Si",
          nip: "19730507 199403 2 003",
          jabatan: "Kasubag Umum dan Kepegawaian",
          bidang: "SEKRETARIAT",
          status: "Aktif",
          keterangan: "Penata Tk. I, III/d"
        },
        {
          id: 3,
          nama: "SRI DEVINA SEMBIRING, S.Sos., M.Si",
          nip: "19710310 199901 2 001",
          jabatan: "Perencana Ahli Muda",
          bidang: "SEKRETARIAT",
          status: "Aktif",
          keterangan: "Pembina, IV/a"
        },
        {
          id: 4,
          nama: "AHMAD FAUZI, S.Kom",
          nip: "19850715 201001 1 008",
          jabatan: "Kepala Bidang",
          bidang: "TIK",
          status: "Aktif",
          keterangan: "Penata, III/c"
        },
        {
          id: 5,
          nama: "DINI PRATIWI, S.T.",
          nip: "19880503 201101 2 012",
          jabatan: "Pranata Komputer Ahli Muda",
          bidang: "TIK",
          status: "Aktif",
          keterangan: "Penata, III/c"
        },
        {
          id: 6,
          nama: "MUHAMMAD RIZKI, S.Kom., M.Cs.",
          nip: "19900228 201503 1 003",
          jabatan: "Pranata Komputer Ahli Muda",
          bidang: "TIK",
          status: "Aktif",
          keterangan: "Penata, III/c"
        },
        {
          id: 7,
          nama: "DEWI SARTIKA, S.Kom",
          nip: "19911105 201609 2 001",
          jabatan: "Pranata Komputer Ahli Pertama",
          bidang: "TIK",
          status: "Aktif",
          keterangan: "Penata Muda Tk. I, III/b"
        },
        {
          id: 8,
          nama: "BUDI SANTOSO, S.T., M.T.",
          nip: "19780605 200501 1 002",
          jabatan: "Kepala Bidang",
          bidang: "PIP",
          status: "Aktif",
          keterangan: "Pembina, IV/a"
        },
        {
          id: 9,
          nama: "RINA MARLINA, S.Kom",
          nip: "19850917 201001 2 015",
          jabatan: "Analis Sistem Informasi Ahli Muda",
          bidang: "PIP",
          status: "Aktif",
          keterangan: "Penata, III/c"
        },
        {
          id: 10,
          nama: "HENDRI KURNIAWAN, S.Kom",
          nip: "19870612 201403 1 005",
          jabatan: "Analis Sistem Informasi Ahli Pertama",
          bidang: "PIP",
          status: "Cuti",
          keterangan: "Penata Muda Tk. I, III/b"
        },
        {
          id: 11,
          nama: "SITI NURHALIZA, S.T., M.MT.",
          nip: "19800423 200604 2 008",
          jabatan: "Kepala Bidang",
          bidang: "E-Government",
          status: "Aktif",
          keterangan: "Pembina, IV/a"
        },
        {
          id: 12,
          nama: "AGUS WIDODO, S.Kom",
          nip: "19830811 200912 1 002",
          jabatan: "Pranata Komputer Ahli Muda",
          bidang: "E-Government",
          status: "Aktif",
          keterangan: "Penata, III/c"
        },
        {
          id: 13,
          nama: "LINDA SAFITRI, S.Kom",
          nip: "19901221 201503 2 002",
          jabatan: "Pranata Komputer Ahli Pertama",
          bidang: "E-Government",
          status: "Aktif",
          keterangan: "Penata Muda Tk. I, III/b"
        },
        {
          id: 14,
          nama: "JOKO SUSILO, S.T., M.T.",
          nip: "19760918 200112 1 003",
          jabatan: "Kepala Bidang",
          bidang: "STANDI",
          status: "Aktif",
          keterangan: "Pembina, IV/a"
        },
        {
          id: 15,
          nama: "ANITA PUSPITASARI, S.Kom.",
          nip: "19860307 201001 2 009",
          jabatan: "Analis Kebijakan Ahli Muda",
          bidang: "STANDI",
          status: "Aktif",
          keterangan: "Penata, III/c"
        },
        {
          id: 16,
          nama: "RUDI HERMAWAN, S.Kom., M.TI.",
          nip: "19890415 201403 1 004",
          jabatan: "Analis Kebijakan Ahli Pertama",
          bidang: "STANDI",
          status: "Cuti",
          keterangan: "Penata Muda Tk. I, III/b"
        },
        {
          id: 17,
          nama: "BAMBANG SURYANTO, S.E.",
          nip: "19720505 199212 1 001",
          jabatan: "Kasubag Keuangan",
          bidang: "SEKRETARIAT",
          status: "Aktif",
          keterangan: "Penata Tk. I, III/d"
        },
        {
          id: 18,
          nama: "YANTI MAULIDA, S.AP.",
          nip: "19900210 201503 2 001",
          jabatan: "Analis Kebijakan",
          bidang: "SEKRETARIAT",
          status: "Aktif",
          keterangan: "Penata Muda, III/a"
        }
      ],
      searchQuery: '',
      selectedBidang: '',
      pagination: {
        currentPage: 1,
        itemsPerPage: 8,
        totalPages: 1
      },
      showEmployeeModal: false,
      showAddModal: false, 
      showEditModal: false,
      selectedEmployee: {},
      formData: {
        id: null,
        nama: '',
        nip: '',
        jabatan: '',
        bidang: '',
        status: 'Aktif',
        keterangan: ''
      },
      loading: false,
      error: null
    };
  },
  computed: {
    bidangList() {
      return [...new Set(this.employees.map(emp => emp.bidang))];
    },
    filteredEmployees() {
      let filtered = [...this.employees];
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(emp => 
          emp.nama.toLowerCase().includes(query) || 
          emp.nip.includes(query) ||
          emp.jabatan.toLowerCase().includes(query)
        );
      }
      
      // Apply bidang filter
      if (this.selectedBidang) {
        filtered = filtered.filter(emp => emp.bidang === this.selectedBidang);
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
    viewEmployee(employee) {
      this.selectedEmployee = { ...employee };
      this.showEmployeeModal = true;
    },
    editEmployee(employee) {
      // Fixed function to open edit modal instead of view modal
      this.openEditModal(employee);
    },
    openEditModal(employee) {
      // Populate form data with selected employee's data
      this.formData = {
        id: employee.id,
        nama: employee.nama, 
        nip: employee.nip,
        jabatan: employee.jabatan,
        bidang: employee.bidang,
        status: employee.status,
        keterangan: employee.keterangan || ''
      };
      // Close employee view modal if it's open
      this.showEmployeeModal = false;
      // Open edit modal
      this.showEditModal = true;
    },
    closeFormModal() {
      // Reset form and close modals
      this.showAddModal = false;
      this.showEditModal = false;
      this.resetForm();
    },
    resetForm() {
      // Reset form data to default values
      this.formData = {
        id: null,
        nama: '',
        nip: '',
        jabatan: '',
        bidang: '',
        status: 'Aktif',
        keterangan: ''
      };
    },
    submitForm() {
      // Form validation
      if (!this.formData.nama || !this.formData.nip || !this.formData.jabatan || !this.formData.bidang) {
        alert('Mohon lengkapi data yang diperlukan!');
        return;
      }

      if (this.showEditModal) {
        // Update existing employee
        const index = this.employees.findIndex(emp => emp.id === this.formData.id);
        if (index !== -1) {
          this.employees[index] = { ...this.formData };
          // Also update selectedEmployee if it's the same employee
          if (this.selectedEmployee.id === this.formData.id) {
            this.selectedEmployee = { ...this.formData };
          }
        }
      } else {
        // Add new employee
        const newId = Math.max(...this.employees.map(emp => emp.id)) + 1;
        const newEmployee = {
          ...this.formData,
          id: newId
        };
        this.employees.push(newEmployee);
      }

      // Close modal and reset form
      this.closeFormModal();
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
    },
    getBidangClass(bidang) {
      const bidangClasses = {
        'SEKRETARIAT': 'badge-primary',
        'PIP': 'badge-info',
        'TIK': 'badge-success',
        'E-Government': 'badge-warning',
        'STANDI': 'badge-danger'
      };
      
      return bidangClasses[bidang] || 'badge-gray';
    }
  }
};
</script>

<style scoped>
```css
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

/* ===== MODAL TAMBAH PEGAWAI ===== */
.add-employee-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.add-employee-modal.active {
  opacity: 1;
  visibility: visible;
}

.add-modal-container {
  width: 90%;
  max-width: 550px;
  background-color: #ffffff;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  transform: translateY(20px);
  transition: transform 0.4s ease;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  border: 1px solid #e2e8f0;
}

.add-employee-modal.active .add-modal-container {
  transform: translateY(0);
}

.add-modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
  position: relative;
}

.add-modal-header::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 100%);
}

.add-modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.add-modal-title-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #dbeafe;
  color: #2563eb;
  font-size: 1rem;
}

.add-modal-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #64748b;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 50%;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
}

.add-modal-close:hover {
  color: #ef4444;
  background-color: #fee2e2;
}

.add-modal-body {
  padding: 1.75rem 1.5rem;
  overflow-y: auto;
}

.add-employee-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-row {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.form-row .form-group {
  flex: 1;
  min-width: 200px;
}

.form-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.label-required::after {
  content: '*';
  color: #ef4444;
  font-weight: 700;
}

.form-input {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  color: #1e293b;
  background-color: #ffffff;
  transition: all 0.3s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.form-input:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.form-input::placeholder {
  color: #94a3b8;
}

.form-select {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  color: #1e293b;
  background-color: #ffffff;
  transition: all 0.3s ease;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2364748b'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd' /%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.5em;
  padding-right: 2.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.form-select:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 0.9375rem;
  color: #1e293b;
  background-color: #ffffff;
  transition: all 0.3s ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.form-textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.form-hint {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.input-error {
  border-color: #ef4444 !important;
}

.error-message {
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

.add-modal-footer {
  padding: 1.25rem 1.5rem;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  background-color: #f8fafc;
}

.btn-cancel {
  padding: 0.625rem 1.25rem;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid #e2e8f0;
  background-color: #f1f5f9;
  color: #334155;
  font-size: 0.875rem;
}

.btn-cancel:hover {
  background-color: #e2e8f0;
}

.btn-save {
  padding: 0.625rem 1.25rem;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  background-color: #2563eb;
  color: white;
  font-size: 0.875rem;
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-save:hover {
  background-color: #1d4ed8;
  transform: translateY(-1px);
  box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
}

.btn-save:active {
  transform: translateY(0);
  box-shadow: 0 1px 2px rgba(37, 99, 235, 0.3);
}

.btn-save-icon {
  font-size: 1.25rem;
  line-height: 1;
}

/* Form input icon */
.input-icon-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 1rem;
}

.input-with-icon {
  padding-left: 2.75rem;
}

/* Avatar preview in form */
.avatar-preview {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.avatar-preview-container {
  width: 6rem;
  height: 6rem;
  border-radius: 50%;
  background-color: #dbeafe;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  font-weight: 600;
  font-size: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 3px solid #ffffff;
  overflow: hidden;
  transition: all 0.3s ease;
}

/* Responsive styling */
@media (max-width: 640px) {
  .form-row {
    flex-direction: column;
    gap: 1.25rem;
  }
  
  .form-row .form-group {
    width: 100%;
  }
  
  .add-modal-container {
    width: 95%;
  }
  
  .add-modal-title {
    font-size: 1.125rem;
  }
  
  .add-modal-body {
    padding: 1.25rem 1rem;
  }
  
  .add-modal-footer {
    padding: 1rem;
  }
}

/* Animation for showing validation errors */
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  20%, 60% { transform: translateX(-5px); }
  40%, 80% { transform: translateX(5px); }
}

.shake-animation {
  animation: shake 0.5s ease-in-out;
}

/* Success state */
.form-success {
  border-color: #10b981 !important;
}

/* Loading state */
.btn-loading {
  position: relative;
  pointer-events: none;
  color: transparent !important;
}

.btn-loading::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 1rem;
  height: 1rem;
  margin-top: -0.5rem;
  margin-left: -0.5rem;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Form sections */
.form-section {
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 1.25rem;
  margin-bottom: 1.25rem;
}

.form-section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #334155;
  margin-bottom: 1rem;
}

/* Field list container */
.field-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Toggle input */
.toggle-container {
  display: flex;
  align-items: center;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 52px;
  height: 28px;
  margin-right: 10px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #cbd5e1;
  transition: .4s;
  border-radius: 34px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 20px;
  width: 20px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background-color: #2563eb;
}

input:focus + .toggle-slider {
  box-shadow: 0 0 1px #2563eb;
}

input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

.toggle-label {
  font-size: 0.875rem;
  color: #475569;
  user-select: none;
}

/* Avatar upload container */
.avatar-upload {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background-color: #f8fafc;
  border-radius: 0.5rem;
  border: 1px dashed #cbd5e1;
}

.upload-button {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  background-color: #dbeafe;
  color: #2563eb;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: none;
}

.upload-button:hover {
  background-color: #bfdbfe;
}

.upload-hint {
  font-size: 0.75rem;
  color: #64748b;
  text-align: center;
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

/* ===== EMPLOYEE PROFILE STYLING ===== */
.employee-profile {
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

.detail-value.description {
  line-height: 1.5;
  white-space: pre-line;
}

/* ===== FORM STYLING ===== */
.employee-form {
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