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
              <button @click="abrirModal(ticket)" :disabled="ticket.estado === 'Atendido'"
                :class="['btn', ticket.estado === 'Atendido' ? 'btn-secondary' : 'btn-success', 'btn-sm']">
                <i class="fas fa-check"></i>
              </button>
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
                  <option value="Ing. Jhoedy Indurain Domínguez Domínguez">Ing. Jhoedy Indurain Domínguez Domínguez
                  </option>
                  <option value="Mtro. José Alberto Díaz López">Mtro. José Alberto Díaz López</option>
                  <option value="Ing. Rusell Emmanuel Canche Ciau">Ing. Rusell Emmanuel Canche Ciau</option>
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


    </div>
  </div>
</template>

<script>
import axios from "axios";
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import { Fancybox } from '@fancyapps/ui';

export default {
  data() {
    return {
      tickets: [],
      categorias: [],
      prioridades: [],
      filtroCategoria: '',
      filtroEstado: '',
      filtroPrioridad: '',
      modalVisible: false,
      ticketSeleccionado: null,
      quienAtendio: '',
      solucion: '',
    };
  },
  mounted() {
    this.fetchTickets();
    this.fetchCategorias();
    this.fetchPrioridades();
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
        const response = await axios.get("/obtenertickets");
        this.tickets = response.data;
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
        alert('Por favor, seleccione quién atendió');
        return;
      }

      if (!this.solucion) {
        alert('Por favor, ingrese la solución u observaciones');
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

        alert('Ticket marcado como atendido correctamente');
        this.cerrarModal();

      } catch (error) {
        console.error('Error al marcar como atendido:', error);
        alert('Hubo un error al actualizar el estado');
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
    }
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