// Publicaciones Técnicas de ejemplo (sin imágenes)
const publicaciones = [
  {
    titulo: "Efecto de la fertilización en maíz bajo condiciones de temporal",
    tituloIngles: "Effect of fertilization on maize under rainfed conditions",
    year: 2023,
    tipo: "pdf",
    portada: "imagenes/portadas/tec01.jpg",
    url: "pdfs/tec01.pdf"
  },
  {
    titulo: "Manejo de plagas en cultivos de frijol en el Altiplano",
    tituloIngles: "Pest management in bean crops in the Altiplano",
    year: 2023,
    tipo: "pdf",
    portada: "imagenes/portadas/tec02.jpg",
    url: "pdfs/tec02.pdf"
  },
  { titulo: "Producción sustentable de nopal verdura", year: 2022, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec03.pdf" },
  { titulo: "Evaluación del uso eficiente del agua en hortalizas", year: 2022, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec04.pdf" },
  { titulo: "Guía para el control de malezas en cultivos básicos", year: 2021, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec05.pdf" },
  { titulo: "Video: Innovaciones técnicas en agricultura protegida", year: 2021, tipo: "video", portada: "imagenes/icopdf.png", url: "https://youtube.com/" },
  { titulo: "Prácticas de conservación de suelo en zonas áridas", year: 2020, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec06.pdf" },
  { titulo: "Métodos de riego tecnificado para zonas semiáridas", year: 2019, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec07.pdf" },
  { titulo: "Guía para la producción de avena forrajera", year: 2019, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec08.pdf" },
  { titulo: "Mejoramiento genético en maíz criollo de Zacatecas", year: 2018, tipo: "pdf", portada: "imagenes/icopdf.png", url: "pdfs/tec09.pdf" }
];

// Parámetros
const porPagina = 12;
let paginaActual = 1;
let publicacionesFiltradas = [...publicaciones];

// Elementos del DOM
const contenedor = document.getElementById('contenedor');
const paginacion = document.getElementById('paginacion');
const buscar = document.getElementById('buscar');
const anio = document.getElementById('anio');
const tipo = document.getElementById('tipo');
const limpiar = document.getElementById('limpiar');

// Generar años únicos
const anios = [...new Set(publicaciones.map(p => p.year))].sort((a, b) => b - a);
anio.innerHTML = '<option value="">Todos los años</option>' + anios.map(y => `<option value="${y}">${y}</option>`).join('');

// Renderizado de tarjetas
function renderPublicaciones() {
  contenedor.innerHTML = '';
  const inicio = (paginaActual - 1) * porPagina;
  const fin = inicio + porPagina;
  const visibles = publicacionesFiltradas.slice(inicio, fin);

  visibles.forEach(p => {
    const card = document.createElement('div');
    card.className = 'col-12 mb-2';
    card.innerHTML = `
      <div class="card card-publicacion">
        <div class="row g-0">
          <div class="col-md-3">
            <img src="${p.portada}" class="img-fluid rounded-start" alt="${p.titulo}">
          </div>
          <div class="col-md-9">
            <div class="card-body d-flex flex-column">
              <h6 class="card-title text-gob fw-bold">${p.titulo}</h6>
              ${p.tituloIngles ? `<p class="text-gob small">${p.tituloIngles}</p>` : ''}
              <p class="text-gob small mb-2">Año: ${p.year}</p>
              <a href="${p.url}" target="_blank" class="mt-auto btn btn-outline-gob btn-sm align-self-start">Ver publicación</a>
            </div>
          </div>
        </div>
      </div>`;
    contenedor.appendChild(card);
  });

  renderPaginacion();
}

// Paginación
function renderPaginacion() {
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
buscar.addEventListener('input', filtrar);
anio.addEventListener('change', filtrar);
tipo.addEventListener('change', filtrar);
limpiar.addEventListener('click', () => {
  buscar.value = '';
  anio.value = '';
  tipo.value = '';
  filtrar();
});

// Inicial
renderPublicaciones();
