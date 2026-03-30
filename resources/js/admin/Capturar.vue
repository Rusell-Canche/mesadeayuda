<template>
    <div class="form-container">
        <div class="caratula-background">
            <form @submit.prevent="submitForm" class="caratula-form">

                <div class="form-row mb-3">
                    <span class="text-danger small fw-bold">* Favor de llenar todos los campos del Ticket</span>
                </div>

                <!-- Título y separador -->
                <div class="form-row mb-3">
                    <h4 class="text-secondary">Información del Ticket</h4>
                    <hr>
                </div>

                <!-- Capturador  -->
                <div class="form-group mb-3">
                    <label for="capturador">Solicitante:</label>
                    <input type="text" id="capturador" v-model="form.capturador" class="form-control" disabled>
                </div>

                <!-- Cargo -->
                <div class="form-group mb-3">
                    <label for="cargo">Cargo:</label>
                    <input type="text" id="cargo" v-model="form.cargo" class="form-control" disabled>
                </div>


                <!-- Inputs en dos columnas -->
                <div class="row">
                    <!-- Asunto -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="asunto">Asunto: *</label>
                        <input type="text" id="asunto" v-model="form.asunto" class="form-control">
                    </div>

                    <!-- Prioridad -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="prioridad">Prioridad: *</label>
                        <select v-model="form.prioridad_id" class="form-select">
                            <option disabled value="">Seleccione una prioridad</option>
                            <option v-for="prioridad in prioridades" :key="prioridad.id" :value="prioridad.id">
                                {{ prioridad.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Unidad Administrativa -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="unidad">Unidad Administrativa:</label>
                        <input type="text" id="unidad" v-model="form.unidad_administrativa" class="form-control"
                            disabled>
                    </div>

                    <!-- Categoría -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="categoria">Categoría: *</label>
                        <select v-model="form.categoria_id" class="form-select">
                            <option disabled value="">Seleccione una categoría</option>
                            <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                {{ categoria.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Archivo adjunto -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="archivo">Archivo adjunto:</label>
                        <input type="file" id="archivo" @change="handleFileUpload" class="form-control">
                        <small class="text-muted">Formatos permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG.</small>
                    </div>

                    <!-- Asignar a -->
                    <div class="form-group col-md-6 mb-3">
                        <label for="asignado">Asignar a: *</label>
                        <select id="asignado" v-model="form.asignado_a" class="form-select">
                            <option disabled value="">Seleccione un responsable</option>
                            <option v-for="u in usuariosTIC" :key="u.id" :value="u.id">
                                {{ u.nombre }} {{ u.apellido_paterno }} {{ u.apellido_materno }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Editor Summernote (solo) -->
                <div class="form-group mb-4">
                    <label for="contenido">Descripción: *</label>
                    <br>
                    <small class="text-muted">Favor de detallar su ticket con los archivos o imagenes necesarias para
                        complementar la descripción.</small>
                    <textarea id="contenido" ref="summernoteEditor"></textarea>
                </div>

                <!-- Botón -->
                <div>
                    <button type="submit" class="btn btn-primary">
                        <span>Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import { onMounted } from 'vue'
import $ from 'jquery'
window.$ = $;
window.jQuery = $;
import 'summernote/dist/summernote-bs4.min.css'
import 'summernote/dist/summernote-bs4.min.js'

export default {
    data() {
        return {
            form: {
                capturador: '',
                cargo: '',
                asunto: '',
                prioridad_id: '',
                categoria_id: '',
                unidad_administrativa: '',
                contenido: '',
                asignado_a: '',
            },
            prioridades: [],
            categorias: [],
            usuariosTIC: [],
        };
    },
    created() {
        this.obtenerPrioridades();
        this.obtenerCategorias();
        this.obtenerUsuariosTIC();
    },
    methods: {

        async obtenerUsuariosTIC() {
            try {
                const response = await axios.get('/usuarios-tic');
                this.usuariosTIC = response.data;
            } catch (error) {
                console.error('Error al cargar usuarios TIC:', error);
            }
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            this.form.archivo = file;
        },

        async obtenerPrioridades() {
            try {
                const response = await axios.get('/obtenerprioridades');
                this.prioridades = response.data;
            } catch (error) {
                console.error('Error al cargar prioridades:', error);
            }
        },
        async obtenerCategorias() {
            try {
                const response = await axios.get('/obtenercategorias');
                this.categorias = response.data;
            } catch (error) {
                console.error('Error al cargar categorías:', error);
            }
        },


        submitForm() {
            this.form.contenido = $(this.$refs.summernoteEditor).summernote('code');

            const formData = new FormData();
            formData.append('capturador', this.form.capturador);
            formData.append('cargo', this.form.cargo);
            formData.append('asunto', this.form.asunto);
            formData.append('prioridad_id', this.form.prioridad_id);
            formData.append('categoria_id', this.form.categoria_id);
            formData.append('unidad_administrativa', this.form.unidad_administrativa);
            formData.append('contenido', this.form.contenido);
            formData.append('asignado_a', this.form.asignado_a);
            if (this.form.archivo) {
                formData.append('archivo', this.form.archivo);
            }

            for (let pair of formData.entries()) {
                console.log(`${pair[0]}:`, pair[1]);
            } axios.post('/nuevoticket', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Ticket creado!',
                        text: 'Tu ticket ha sido creado exitosamente.',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#0d6efd'
                    });
                    this.resetForm();
                })
                .catch(error => {
                    console.error('Error al crear ticket:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al crear el ticket. Por favor, intenta nuevamente.',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#0d6efd'
                    });
                });
        },

        resetForm() {
            this.form = {
                capturador: this.form.capturador,
                cargo: this.form.cargo,
                asunto: '',
                prioridad_id: '',
                unidad_administrativa: this.form.unidad_administrativa,
                categoria_id: '',
                contenido: '',
                asignado_a: '',
            };
            $(this.$refs.summernoteEditor).summernote('reset');
        },

        fetchCurrentUser() {
            axios.get('/currentuser')
                .then(response => {
                    const currentUser = response.data;

                    this.form.capturador = `${currentUser.nombre} ${currentUser.apellido_paterno} ${currentUser.apellido_materno}`;
                    this.form.cargo = currentUser.cargo || 'Sin cargo';
                    this.form.unidad_administrativa = currentUser.departamento?.nombre || 'Sin departamento';
                })
                .catch(error => {
                    console.error('Error al obtener el usuario actual:', error);
                });
        },


    },
    mounted() {
        this.fetchCurrentUser();

        $(this.$refs.summernoteEditor).summernote({
            placeholder: 'Escribe el contenido aquí...',
            tabsize: 2,
            height: 200,
            toolbar: [
                ['style', ['bold', 'italic', 'underline', 'clear']],
                ['font', ['strikethrough', 'superscript', 'subscript']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['view', ['fullscreen']]
            ]
        });
    }
};
</script>

<style>
.caratula-form {
    background: rgba(255, 255, 255, 0.7);
    padding: 40px;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}
</style>
