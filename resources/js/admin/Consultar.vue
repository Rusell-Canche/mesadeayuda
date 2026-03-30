<template>
  <div class="form-container">
    <div class="container-fluid mt-4" style="overflow-x:auto;">
      <div class="row mb-3">
        <div class="col-md-4">
          <label for="filtroCategoria" class="form-label fw-bold">Categoría</label>
          <select id="filtroCategoria" v-model="filtroCategoria" class="form-select">
            <option value="">Todas las categorías</option>
            <option v-for="cat in categorias" :key="cat.id" :value="cat.nombre">
              {{ cat.nombre }}
            </option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="filtroEstado" class="form-label fw-bold">Estado</label>
          <select id="filtroEstado" v-model="filtroEstado" class="form-select">
            <option value="">Todos los estados</option>
            <option value="Abierto">Abierto</option>
            <option value="Atendido">Atendido</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="filtroPrioridad" class="form-label fw-bold">Prioridad</label>
          <select id="filtroPrioridad" v-model="filtroPrioridad" class="form-select">
            <option value="">Todas las prioridades</option>
            <option v-for="prio in prioridades" :key="prio.id" :value="prio.nombre">
              {{ prio.nombre }}
            </option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <button class="btn btn-secondary" @click="limpiarFiltros"
          style="background-color: #0d6efd; color: white; border-color: #0d6efd;">
          Mostrar todo
        </button>
        <button class="btn btn-warning ms-2" @click="verEliminados">
          Ver eliminados
        </button>
      </div>

      <table
        class="table table-bordered table-striped table-vcenter js-dataTable-full dataTable no-footer dtr-inline collapsed">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Asunto</th>
            <th>Solicitante</th>
            <th>Cargo</th>
            <th>Prioridad</th>
            <th>Unidad Administrativa</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Fecha creación</th>
            <th>Fecha cierre</th>
            <th>Archivo</th>
            <th>Oficio</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in ticketsFiltrados" :key="ticket.id">
            <td>{{ ticket.id }}</td>
            <td :title="ticket.asunto" style="max-width: 180px;">{{ ticket.asunto }}</td>
            <td :title="ticket.capturador" style="max-width: 150px;">{{ ticket.capturador }}</td>
            <td :title="ticket.cargo" style="max-width: 150px;">{{ ticket.cargo }}</td>

            <td>
              <span class="badge p-2"
                :style="{ backgroundColor: ticket.prioridad?.color || '#6c757d', color: 'white' }">
                {{ ticket.prioridad?.nombre || 'Sin prioridad' }}
              </span>
            </td>

            <td :title="ticket.unidad_administrativa" style="max-width: 180px;">
              {{ ticket.unidad_administrativa }}
            </td>
            <td :title="ticket.categoria?.nombre" style="max-width: 150px;">
              {{ ticket.categoria?.nombre || 'Sin categoría' }}
            </td>

            <td>
              <span class="badge" :class="{
                'bg-success': ticket.estado === 'Abierto',
                'bg-warning': ticket.estado === 'Pendiente',
                'bg-danger': ticket.estado === 'Atendido',
              }">
                {{ ticket.estado }}
              </span>
            </td>

            <td>{{ formatFecha(ticket.created_at) }}</td>
            <td>
              <span v-if="ticket.fecha_cierre">
                {{ formatFecha(ticket.fecha_cierre) }}
              </span>
              <span v-else class="badge bg-secondary text-white" style="padding: 0.4em 0.7em; font-size: 0.85rem;">
                Sin Cerrar
              </span>
            </td>


            <td>
              <a v-if="ticket.archivo_path" href="#" @click.prevent="openFancybox(ticket, 'archivo')"
                title="Ver archivo" class="d-inline-flex align-items-center justify-content-center bg-primary"
                style="width: 28px; height: 28px; border-radius: 4px; color: white; text-decoration: none;">
                <i class="fas fa-eye"></i>
              </a>
              <span v-else>—</span>
            </td>

            <td>
              <a v-if="ticket.oficio_atendido" href="#" @click.prevent="openFancybox(ticket, 'oficio')"
                title="Ver oficio" class="d-inline-flex align-items-center justify-content-center bg-success"
                style="width: 28px; height: 28px; border-radius: 4px; color: white; text-decoration: none;">
                <i class="fas fa-file-pdf"></i>
              </a>
              <span v-else class="badge bg-secondary text-white">Sin Atender</span>
            </td>

            <td>
              <!-- MODO NORMAL -->
              <template v-if="!modoEliminados">

                <button @click="abrirModalEditar(ticket)" :disabled="ticket.estado === 'Atendido'"
                  :class="['btn', ticket.estado === 'Atendido' ? 'btn-secondary' : 'btn-warning', 'btn-sm', 'me-1']"
                  title="Editar ticket">
                  <i class="fas fa-edit"></i>
                </button>

                <button @click="abrirModal(ticket)" :disabled="ticket.estado === 'Atendido'"
                  :class="['btn', ticket.estado === 'Atendido' ? 'btn-secondary' : 'btn-success', 'btn-sm']">
                  <i class="fas fa-check"></i>
                </button>

                <button @click="eliminarTicket(ticket.id)" :disabled="ticket.estado !== 'Atendido'"
                  class="btn btn-danger btn-sm ms-1">
                  <i class="fas fa-trash"></i>
                </button>

              </template>

              <!-- MODO ELIMINADOS -->
              <template v-else>

                <button @click="restaurarTicket(ticket.id)" class="btn btn-info btn-sm">
                  <i class="fas fa-undo"></i>
                </button>

              </template>
            </td>

          </tr>
        </tbody>
      </table>
      <!-- Modal -->
      <div v-if="modalVisible" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);"
        @click.self="cerrarModal">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Marcar ticket como atendido</h5>
              <button type="button" class="btn-close" @click="cerrarModal"></button>
            </div>
            <div class="modal-body">
              <p><strong>Asunto:</strong> {{ ticketSeleccionado?.asunto }}</p>

              <div class="mb-3">
                <label for="quienAtendio" class="form-label">¿Quién atendió?</label>
                <select id="quienAtendio" v-model="quienAtendio" class="form-select">
                  <option disabled value="">Seleccione una opción</option>
                  <option v-for="u in usuariosTIC" :key="u.id"
                    :value="`${u.nombre} ${u.apellido_paterno} ${u.apellido_materno}`">
                    {{ u.nombre }} {{ u.apellido_paterno }} {{ u.apellido_materno }}
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label for="solucion" class="form-label">Solución y/o Observaciones</label>
                <textarea id="solucion" v-model="solucion" class="form-control" rows="3"
                  placeholder="Escribe aquí la solución o alguna observación"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="cerrarModal">Cancelar</button>
              <button class="btn btn-primary" :disabled="!quienAtendio" @click="confirmarAtendido">Confirmar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Editar Ticket -->
      <div v-if="modalEditarVisible" class="modal fade show d-block" tabindex="-1"
        style="background-color: rgba(0,0,0,0.5);" @click.self="cerrarModalEditar">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Editar Ticket #{{ ticketEditando?.id }}</h5>
              <button type="button" class="btn-close" @click="cerrarModalEditar"></button>
            </div>
            <div class="modal-body">

              <!-- Asunto -->
              <div class="mb-3">
                <label class="form-label fw-bold">Asunto: *</label>
                <input type="text" v-model="formEditar.asunto" class="form-control"
                  placeholder="Escribe el asunto del ticket">
              </div>

              <div class="row">
                <!-- Prioridad -->
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Prioridad: *</label>
                  <select v-model="formEditar.prioridad_id" class="form-select">
                    <option disabled value="">Seleccione una prioridad</option>
                    <option v-for="p in prioridades" :key="p.id" :value="p.id">
                      {{ p.nombre }}
                    </option>
                  </select>
                </div>

                <!-- Categoría -->
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Categoría: *</label>
                  <select v-model="formEditar.categoria_id" class="form-select">
                    <option disabled value="">Seleccione una categoría</option>
                    <option v-for="c in categorias" :key="c.id" :value="c.id">
                      {{ c.nombre }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Archivo adjunto -->
              <div class="mb-3">
                <label class="form-label fw-bold">Reemplazar archivo adjunto:</label>
                <input type="file" @change="handleFileEditarUpload" class="form-control">
                <small class="text-muted">
                  Formatos permitidos: PDF, DOC, DOCX, JPG, JPEG, PNG.
                  <span v-if="ticketEditando?.archivo_path" class="text-info ms-2">
                    <i class="fas fa-paperclip"></i> Ya tiene un archivo adjunto (déjalo vacío para conservarlo).
                  </span>
                </small>
              </div>

              <!-- Descripción (textarea simple, sin Summernote para evitar conflictos) -->
              <div class="mb-3">
                <label class="form-label fw-bold">Descripción: *</label>
                <textarea v-model="formEditar.contenido" class="form-control" rows="5"
                  placeholder="Describe el ticket..."></textarea>
              </div>

            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" @click="cerrarModalEditar">Cancelar</button>
              <button class="btn btn-primary" @click="guardarEdicion"
                :disabled="!formEditar.asunto || !formEditar.prioridad_id || !formEditar.categoria_id || !formEditar.contenido">
                <i class="fas fa-save me-1"></i> Guardar cambios
              </button>
            </div>
          </div>
        </div>
      </div>


    </div>
  </div>
</template>

<script>
import axios from "axios";
import Swal from 'sweetalert2';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import { Fancybox } from '@fancyapps/ui';

export default {
  data() {
    return {
      tickets: [],
      categorias: [],
      prioridades: [],
      usuariosTIC: [],
      filtroCategoria: '',
      filtroEstado: '',
      filtroPrioridad: '',
      modalVisible: false,
      ticketSeleccionado: null,
      quienAtendio: '',
      solucion: '',
      modalEditarVisible: false,
      ticketEditando: null,
      formEditar: {
        asunto: '',
        prioridad_id: '',
        categoria_id: '',
        contenido: '',
        archivo: null,
      },
    };
  },
  mounted() {
    this.fetchTickets();
    this.fetchCategorias();
    this.fetchPrioridades();
    this.fetchUsuariosTIC();
  },
  computed: {
    ticketsFiltrados() {
      return this.tickets.filter(ticket => {
        return (
          (!this.filtroCategoria || (ticket.categoria && ticket.categoria.nombre === this.filtroCategoria)) &&
          (!this.filtroEstado || ticket.estado === this.filtroEstado) &&
          (!this.filtroPrioridad || (ticket.prioridad && ticket.prioridad.nombre === this.filtroPrioridad))
        );
      });
    },
  },
  methods: {
    async fetchUsuariosTIC() {
      try {
        const response = await axios.get('/usuarios-tic');
        this.usuariosTIC = response.data;
      } catch (error) {
        console.error('Error al cargar usuarios TIC:', error);
      }
    },
    abrirModal(ticket) {
      this.ticketSeleccionado = ticket;
      this.quienAtendio = '';
      this.solucion = '';
      this.modalVisible = true;
    },
    cerrarModal() {
      this.modalVisible = false;
      this.ticketSeleccionado = null;
      this.quienAtendio = '';
      this.solucion = '';
    },

    async fetchTickets() {
      try {
        const response = await axios.get("/mis-tickets");
        this.tickets = response.data;
        this.modoEliminados = false;
      } catch (error) {
        console.error("Error al cargar tickets:", error);
      }
    },
    async fetchCategorias() {
      try {
        const response = await axios.get("/obtenercategorias");
        this.categorias = response.data;
      } catch (error) {
        console.error("Error al cargar categorías:", error);
      }
    },
    async fetchPrioridades() {
      try {
        const response = await axios.get("/obtenerprioridades");
        this.prioridades = response.data;
      } catch (error) {
        console.error("Error al cargar prioridades:", error);
      }
    },
    formatFecha(fecha) {
      if (!fecha) return '';
      return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
      });
    },
    limpiarFiltros() {
      this.filtroCategoria = '';
      this.filtroEstado = '';
      this.filtroPrioridad = '';
      this.fetchTickets();
    },
    marcarAtendido(ticket) {
      if (ticket.estado === 'Atendido') return; // evita abrir modal si ya está atendido
      this.ticketSeleccionado = ticket;
      this.quienAtendio = '';
      this.solucion = '';
      this.modalVisible = true;
    },

    async confirmarAtendido() {
      if (!this.quienAtendio) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Por favor, seleccione quién atendió' });
        return;
      }

      if (!this.solucion) {
        Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Por favor, ingrese la solución u observaciones' });
        return;
      }

      try {
        const response = await axios.put(`/ticketatendido/${this.ticketSeleccionado.id}`, {
          quien_atendio: this.quienAtendio,
          solucion: this.solucion
        });

        // Actualizas los datos en el arreglo de tickets
        const ticket = this.tickets.find(t => t.id === this.ticketSeleccionado.id);
        if (ticket) {
          ticket.estado = 'Atendido';
          ticket.fecha_cierre = response.data.fecha_cierre;
          ticket.quien_atendio = this.quienAtendio;
          ticket.solucion = this.solucion;
          ticket.oficio_atendido = response.data.oficio_atendido || null;
        }

        Swal.fire({ icon: 'success', title: '¡Listo!', text: 'Ticket marcado como atendido correctamente' });
        this.cerrarModal();

      } catch (error) {
        console.error('Error al marcar como atendido:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Hubo un error al actualizar el estado' });
      }
    },

    openFancybox(ticket, tipo) {
      let src = '';

      if (tipo === 'archivo' && ticket.archivo_path) {
        src = `/storage/${ticket.archivo_path}`;
      } else if (tipo === 'oficio' && ticket.oficio_atendido) {
        // Si la ruta es relativa o absoluta, ajusta según corresponda
        src = `/${ticket.oficio_atendido}`; // Ya incluye 'storage/oficios/...' completo
      } else {
        return; // No hay archivo/oficio para mostrar
      }

      const esPdf = src.toLowerCase().endsWith('.pdf');

      Fancybox.show([
        {
          src,
          type: esPdf ? 'pdf' : 'image',
          caption: tipo === 'archivo' ? ticket.asunto || 'Archivo adjunto' : 'Oficio atendido',
        }
      ]);
    },

    async eliminarTicket(id) {
      const result = await Swal.fire({
        title: '¿Enviar a la papelera?',
        text: 'El ticket será enviado a la papelera.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      });

      if (!result.isConfirmed) return;

      try {
        await axios.delete(`/eliminarticket/${id}`);

        this.tickets = this.tickets.filter(t => t.id !== id);

        Swal.fire({
          icon: 'success',
          title: '¡Enviado!',
          text: 'Ticket enviado a la papelera.',
          confirmButtonColor: '#0d6efd'
        });

      } catch (error) {
        console.error(error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'No se pudo eliminar el ticket. Intenta nuevamente.',
          confirmButtonColor: '#d33'
        });
      }
    },

    async verEliminados() {
      try {
        const res = await axios.get('/tickets-eliminados');
        this.tickets = res.data;
        this.modoEliminados = true;
      } catch (error) {
        console.error(error);
      }
    },

    async restaurarTicket(id) {
      try {
        await axios.put(`/restaurarticket/${id}`);
        this.fetchTickets();
      } catch (error) {
        console.error(error);
      }
    },

    abrirModalEditar(ticket) {
      this.ticketEditando = ticket;
      this.formEditar = {
        asunto: ticket.asunto,
        prioridad_id: ticket.prioridad?.id || '',
        categoria_id: ticket.categoria?.id || '',
        // Limpia etiquetas HTML de Summernote para el textarea plano
        contenido: ticket.contenido
          ? ticket.contenido.replace(/<[^>]*>/g, '')
          : '',
        archivo: null,
      };
      this.modalEditarVisible = true;
    },

    cerrarModalEditar() {
      this.modalEditarVisible = false;
      this.ticketEditando = null;
      this.formEditar = { asunto: '', prioridad_id: '', categoria_id: '', contenido: '', archivo: null };
    },

    handleFileEditarUpload(event) {
      this.formEditar.archivo = event.target.files[0] || null;
    },

    async guardarEdicion() {
      if (!this.formEditar.asunto || !this.formEditar.prioridad_id ||
        !this.formEditar.categoria_id || !this.formEditar.contenido) {
        Swal.fire({ icon: 'warning', title: 'Campos incompletos', text: 'Por favor completa todos los campos requeridos.' });
        return;
      }

      const formData = new FormData();
      formData.append('asunto', this.formEditar.asunto);
      formData.append('prioridad_id', this.formEditar.prioridad_id);
      formData.append('categoria_id', this.formEditar.categoria_id);
      formData.append('contenido', this.formEditar.contenido);
      if (this.formEditar.archivo) {
        formData.append('archivo', this.formEditar.archivo);
      }

      try {
        const response = await axios.post(
          `/editarticket/${this.ticketEditando.id}`,
          formData,
          { headers: { 'Content-Type': 'multipart/form-data' } }
        );

        // Refleja los cambios en la tabla sin recargar
        const idx = this.tickets.findIndex(t => t.id === this.ticketEditando.id);
        if (idx !== -1) {
          this.tickets[idx] = { ...this.tickets[idx], ...response.data };
        }

        Swal.fire({ icon: 'success', title: '¡Actualizado!', text: 'Ticket actualizado correctamente.' });
        this.cerrarModalEditar();
        this.fetchTickets(); // refresca para obtener relaciones actualizadas
      } catch (error) {
        console.error('Error al editar ticket:', error);
        Swal.fire({ icon: 'error', title: 'Error', text: 'Hubo un error al guardar los cambios.' });
      }
    },
  }
};
</script>



<style scoped>
.priority-dot {
  display: inline-block;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  vertical-align: middle;
  border: 1px solid #333;
}

.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

table th,
table td {
  vertical-align: middle !important;
  padding: 0.5rem 0.75rem;
  font-size: 0.9rem;
}

.form-container {
  background: rgba(255, 255, 255, 0.7);
  padding: 40px;
  box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
}
</style>