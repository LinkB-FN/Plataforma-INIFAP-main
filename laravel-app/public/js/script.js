// Migrated script.js (slightly adapted path expectations)
// Publicaciones del HTML original (aquí pondré solo unas cuantas como ejemplo)
const publicaciones = [
  {
    titulo: "Funcionalidad de las cáscaras de la tuna Roja Lisa Parte II, in vivo",
    tituloIngles: "Functionality of Red Smooth Prickly Pear Peels Part II, in vivo",
    year: 2023,
    tipo: "pdf",
    portada: "imagenes/FT121 MHer.png",
    url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=169&t=1"
  },
  {
    titulo: "Efecto antidiabético de tallarines con harina extruida de cotiledones de frijol",
    tituloIngles: "Antidiabetic effect of noodles with extruded cotyledon bean flour",
    year: 2023,
    tipo: "pdf",
    portada: "imagenes/FT120 RCruz.png",
    url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=168&t=1"
  },
  {
    titulo: "Tecnología para la producción de maíz en condiciones de temporal en Zacatecas",
    year: 2022,
    tipo: "pdf",
    portada: "imagenes/FP46 RSan.png",
    url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=165&t=1"
  }
];

// Parámetros
const porPagina = 12;
let paginaActual = 1;
let publicacionesFiltradas = [...publicaciones];

// Elementos DOM
const contenedor = document.getElementById('contenedor');
const paginacion = document.getElementById('paginacion');
const buscar = document.getElementById('buscar');
const anio = document.getElementById('anio');
const tipo = document.getElementById('tipo');
const limpiar = document.getElementById('limpiar');

// Generar años únicos
if (anio) {
  const anios = [...new Set(publicaciones.map(p => p.year))].sort((a,b)=>b-a);
  anio.innerHTML = '<option value="">Todos los años</option>' + anios.map(y => `<option value="${y}">${y}</option>`).join('');
}

// Renderizado
function renderPublicaciones() {
  if (!contenedor) return;
  contenedor.innerHTML = '';
  const inicio = (paginaActual - 1) * porPagina;
  const fin = inicio + porPagina;
  const visibles = publicacionesFiltradas.slice(inicio, fin);

  visibles.forEach(p => {
    const card = document.createElement('div');
    card.className = 'col-md-4';
    card.innerHTML = `
      <div class="card card-publicacion h-100">
        <img src="/${p.portada}" class="card-img-top" alt="${p.titulo}">
        <div class="card-body d-flex flex-column">
          <h6 class="card-title text-gob fw-bold">${p.titulo}</h6>
          ${p.tituloIngles ? `<p class="text-gob small">${p.tituloIngles}</p>` : ''}
          <p class="text-gob small mb-2">Año: ${p.year}</p>
          <a href="${p.url}" target="_blank" class="mt-auto btn btn-outline-gob btn-sm">Ver publicación</a>
        </div>
      </div>`;
    contenedor.appendChild(card);
  });

  renderPaginacion();
}

// Paginación
function renderPaginacion() {
  if (!paginacion) return;
  const totalPaginas = Math.ceil(publicacionesFiltradas.length / porPagina);
  paginacion.innerHTML = '';
  for (let i = 1; i <= totalPaginas; i++) {
    const li = document.createElement('li');
    li.className = `page-item ${i === paginaActual ? 'active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
    li.onclick = (e) => {
      e.preventDefault();
      paginaActual = i;
      renderPublicaciones();
    };
    paginacion.appendChild(li);
  }
}

// Filtros
function filtrar() {
  if (!buscar || !anio || !tipo) return;
  const q = buscar.value.toLowerCase();
  const y = anio.value;
  const t = tipo.value;
  publicacionesFiltradas = publicaciones.filter(p =>
    (!y || p.year == y) &&
    (!t || p.tipo === t) &&
    ((p.titulo + ' ' + (p.tituloIngles || '')).toLowerCase().includes(q))
  );
  paginaActual = 1;
  renderPublicaciones();
}

// Eventos
if (buscar) buscar.addEventListener('input', filtrar);
if (anio) anio.addEventListener('change', filtrar);
if (tipo) tipo.addEventListener('change', filtrar);
if (limpiar) limpiar.addEventListener('click', () => {
  if (!buscar || !anio || !tipo) return;
  buscar.value = '';
  anio.value = '';
  tipo.value = '';
  filtrar();
});

// Inicial
document.addEventListener('DOMContentLoaded', () => {
  renderPublicaciones();
});
