<template>
    <div>
        <h1>Book List</h1>
        <div>
            <button type="button" class="btn-badge-edit " @click="openEditModal(null, false)">Add New Book</button>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Published On</th>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>Operations</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(book, index) in booksList.data" :key="book.id">
                    <td>{{ index + 1 }}</td>
                    <td>
                        <img :src="fullImagePath(image_path)" width="50" />
                    </td>
                    <td>{{ book.title }}</td>
                    <td>{{ book.author }}</td>
                    <td>{{ book.year_published }}</td>
                    <td>{{ book.isbn }}</td>
                    <td>{{ book.category ? book.category.name : 'Uncategorized' }}</td>
                    <td>
                        <button @click="openEditModal(book, false)" class="btn-badge-edit">
                            Edit
                        </button>
                    </td>
                    <td>
                        <button class="btn-badge-view" @click="getBook(book.id)">
                            View
                        </button>
                    </td>
                    <td>
                        <button v-if="book.deleted_at == null" class="btn-badge-delete"
                            @click="softDeleteBook(book.id)">
                            Delete
                        </button>
                        <labe v-else>Deleted</labe>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="mt-4">
            <button v-if="booksList.data.prev_page_url"
                @click="fetchBooks(booksList.data.first_page_url)">Previous</button>
            <button v-if="booksList.next_page_url" @click="fetchBooks(booksList.data.last_page_url)">Next</button>
        </div>
        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 modal-overlay flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg  w-full w-4/5 max-w-3xl">
                <button @click="toggleModal" class="text-red-500 float-right p-2 bg-gray-200 rounded-full">X</button>
                <h3 class="text-lg font-bold mb-4">{{ this.title }}</h3>
                <hr>
                <div class="flex w-full gap-8">
                    <form @submit.prevent="submitForm" enctype="multipart/form-data" class="col-span-2 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Column 1 -->
                            <div class="space-y-4">
                                <div>
                                    <label for="title" class="block text-sm font-medium">Title</label>
                                    <input :disabled="isDisabled" v-model="form.title" type="text" id="title"
                                        class="w-full p-2 border rounded" required />
                                    <span v-if="errors.title" class="text-red-500">{{ errors.title[0] }}</span>
                                </div>

                                <div>
                                    <label for="author" class="block text-sm font-medium">Author</label>
                                    <input :disabled="isDisabled" v-model="form.author" type="text" id="author"
                                        class="w-full p-2 border rounded" required />
                                    <span v-if="errors.author" class="text-red-500">{{ errors.author[0] }}</span>
                                </div>
                            </div>

                            <!-- Column 2 -->
                            <div class="space-y-4">
                                <div>
                                    <label for="year_published" class="block text-sm font-medium">Published Year</label>
                                    <input :disabled="isDisabled" v-model="form.year_published" type="number"
                                        id="year_published" class="w-full p-2 border rounded" required />
                                    <span v-if="errors.year_published" class="text-red-500">{{ errors.year_published[0]
                                        }}</span>
                                </div>
                                <div>
                                    <label for="title" class="block text-sm font-medium">ISBN</label>
                                    <input :disabled="isDisabled" v-model="form.isbn" type="text" id="title"
                                        class="w-full p-2 border rounded" required />
                                    <span v-if="errors.isbn" class="text-red-500">{{ errors.isbn[0] }}</span>
                                </div>
                                <div>
                                    <label for="category" class="block text-sm font-medium">Category</label>
                                    <select :disabled="isDisabled" v-model="form.category_id" id="category"
                                        class="w-full p-2 border rounded" required>
                                        <option disabled value="">Select Category</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}</option>
                                    </select>
                                    <span v-if="errors.category_id" class="text-red-500">{{ errors.category_id[0]
                                        }}</span>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium">Description</label>
                                    <textarea :disabled="isDisabled" v-model="form.description" id="description"
                                        rows="4" class="w-full p-2 border rounded"></textarea>
                                </div>
                            </div>

                            <!-- Column 3 -->
                            <div class="space-y-4">
                                <div v-if="!isDisabled">
                                    <label for="cover" class="block text-sm font-medium">Book Cover</label>
                                    <input type="file" @change="handleFileUpload" id="cover"
                                        class="w-full p-2 border rounded" />
                                </div>
                            </div>
                        </div>

                        <!-- Save /save ChangesButton -->
                        <div class="flex justify-end mt-4">
                            <button v-if="update" class="btn-badge-save" @click="updateBookData()">Save Changes</button>
                            <button v-if="store" class="btn-badge-save" @click="storeBookData()">Save
                            </button>
                        </div>
                    </form>
                    <div class="flex justify-center items-center p-4">
                        <img :src="this.form.imageUrl" alt="Book Cover Preview"
                            class="w-40 h-40 object-cover shadow-md" />
                        <!-- <img :src="form.coverPreview || 'default-cover.jpg'" alt="Book Cover Preview"
                            class="w-full h-auto rounded-lg shadow-md" /> -->
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    props: {
        viewData: Object,
        required: true,
        default: () => ({ books: {}, categories: [] })
    },
    name: 'BookList',
    data() {
        return {
            showModal: false,
            form: {
                id: null,
                title: '',
                author: '',
                year_published: '',
                category_id: '',
                description: '',
                image: null,
                isbn: '',
                imageUrl: null
            },
            // imageUrl: '/storage/images/default-cover.jpg',
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            update: false,
            store: false,
            isDisabled: false,
            errors: {},
            message: null,
            error: '',
            title: null,
            file: null
        };
    },
    computed: {
        booksList() {
            return this.viewData?.books || { data: [] };
        },
        categories() {
            return this.viewData?.categories || [];
        },
        isFormValid() {
            return this.form.title && this.form.author && this.form.year_published && this.form.category_id && this.form.isbn;
        },
        fullImagePath(image_path) {
            return `storage/${image_path}`;
        }
    },
    methods: {
        validateField(field) {
            if (!this.form[field]) {
                this.errors[field] = `${field.replace('_', ' ')} is required.`;
            } else {
                this.errors[field] = '';
            }
        },
        async fetchBooks(url) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                this.booksList = data;
            } catch (error) {
                console.error(error);
            }
        },
        async getBook(id) {
            try {
                const response = await axios.get(`/books/${id}`);
                this.books = response.data;
                if (response.data) {
                    console.log(response.data);
                    this.openEditModal(response.data, true);
                } else {
                    alert('Book Details Not Found');
                }
            } catch (error) {
                this.error = 'Error fetching books data.';
                console.error('Error:', error);

            }
        },
        async updateBookData() {
            if (!this.isFormValid) {
                this.error = 'Please fill out all required fields.';
                return;
            }
            try {
                const response = await axios.put(`/books/${this.form.id}`, this.form, {
                    headers: { 'X-CSRF-TOKEN': this.csrfToken }
                });
                this.message = response.data.message;
                this.errors = {};
                this.error = '';
                alert(this.message);
            } catch (error) {
                console.log(error);
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                } else {
                    console.error('Error updating data:');
                }
            }
        },
        async storeBookData() {
            if (!this.isFormValid) {
                this.error = 'Please fill out all required fields.';
                return;
            }
            try {

                const formData = new FormData();
                // Append each form field to FormData
                formData.append("title", this.form.title);
                formData.append("author", this.form.author);
                formData.append("year_published", this.form.year_published);
                formData.append("isbn", this.form.isbn);
                formData.append("category_id", this.form.category_id);
                formData.append("description", this.form.description);
                formData.append("image", this.file);

                const response = await axios.post(`/books`, formData, {
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken,
                        'Content-Type': 'multipart/form-data'
                    }
                });
                if (response.status === 201) {
                    this.errors = {};
                    alert(response.data.message);
                }

                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors;
                }

            } catch (error) {

                if (error.response && error.response.data.error) {
                    // Handle other errors returned from the server 
                    this.error = error.response.data.error; alert(this.error);
                }
                else {
                    // Log the entire error response for debugging 
                    console.error('Error saving data:', error.response || error.message || error);
                    this.error = 'An unexpected error occurred.';
                    alert(this.error);
                }
            }
        },

        async softDeleteBook(bookId) {

            if (confirm('Are you sure you want to delete this book?')) {
                const response = await axios.delete(`/books/${bookId}`);
                this.message = response.data.message;
                if (response.data.bookId) {
                    alert(this.message);
                    const book = this.books.find(b => b.id === bookId);
                    if (book) book.deleted_at = new Date();
                } else if (response.data.denide) {
                    alert(response.data.denide);
                }
            }
        },
        openEditModal(book = null, isView) {

            if (book) {
                this.form.id = book.id;
                this.form.title = book.title;
                this.form.author = book.author;
                this.form.year_published = book.year_published;
                this.form.category_id = book.category ? book.category.id : '';
                this.form.description = book.description;
                this.form.imageUrl = book.image_path;
                this.form.isbn = book.isbn;
                if (isView) {
                    this.isDisabled = true;
                    this.update = false;
                    this.store = false;
                    this.title = "Book's Details";
                } else {
                    this.update = true;
                    this.store = false;
                    this.title = "Edit Book Details";
                }

            } else {
                this.update = false;
                this.store = true;
                this.title = "Add New Book";
            }


            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
        },
        handleFileUpload(event) {
            this.file = event.target.files[0];
            console.log(this.file);
            if (this.file) {
                this.form.imageUrl = URL.createObjectURL(this.file);
            }
        },
        toggleModal() {
            this.showModal = !this.showModal;
            if (!this.showModal) {
                this.form.id = null;
                this.form.title = null;
                this.form.author = null;
                this.form.year_published = null;
                this.form.category_id = null;
                this.form.description = null;
                this.form.cover = null;
                this.form.isbn = null;
            }
        }
    }
};
</script>

<style scoped>
h1 {
    color: #3490dc;
}

table {
    width: 100%;
    margin-top: 20px;
}

td {
    vertical-align: middle;
}

.modal-content {
    width: 65% !important;
    padding: 20px;
    margin: 20px auto;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-close-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 8px;
    background-color: #f0f0f0;
    border-radius: 50%;
    cursor: pointer;
}

.form-group {
    margin-bottom: 1rem;
}

.modal-body {
    padding: 20px;
}
</style>
