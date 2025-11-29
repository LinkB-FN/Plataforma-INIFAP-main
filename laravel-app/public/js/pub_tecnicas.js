// Versión limpia de pub_tecnicas.js para Laravel public folder
// Ajusta aquí la fuente de datos si quieres pasar publicaciones desde el servidor.
const PUBLICACIONES = window.PUBLICACIONES || [
  { titulo: "Efecto de la fertilización en maíz bajo condiciones de temporal", tituloIngles: "Effect of fertilization on maize under rainfed conditions", year: 2023, tipo: "pdf", portada: "/imagenes/FT121 MHer.png", url: "/pdfs/tec01.pdf" },
  { titulo: "Manejo de plagas en cultivos de frijol en el Altiplano", tituloIngles: "Pest management in bean crops in the Altiplano", year: 2023, tipo: "pdf", portada: "/imagenes/FT120 RCruz.png", url: "/pdfs/tec02.pdf" }
];

const porPaginaPT = 12;
let paginaActualPT = 1;
let publicacionesFiltradasPT = [...PUBLICACIONES];

function renderPublicacionesPT(containerId = 'contenedor', pagId = 'paginacion') {
  const contenedor = document.getElementById(containerId);
  const paginacion = document.getElementById(pagId);
  if (!contenedor || !paginacion) return;

  contenedor.innerHTML = '';
  const inicio = (paginaActualPT - 1) * porPaginaPT;
  const visibles = publicacionesFiltradasPT.slice(inicio, inicio + porPaginaPT);

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

  // paginación
  paginacion.innerHTML = '';
  const totalPaginas = Math.ceil(publicacionesFiltradasPT.length / porPaginaPT) || 1;
  for (let i = 1; i <= totalPaginas; i++) {
    const li = document.createElement('li');
    li.className = `page-item ${i === paginaActualPT ? 'active' : ''}`;
    li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
    li.onclick = (e) => { e.preventDefault(); paginaActualPT = i; renderPublicacionesPT(containerId, pagId); };
    paginacion.appendChild(li);
  }
}

function setupFiltrosPT(buscarId='buscar', anioId='anio', tipoId='tipo', limpiarId='limpiar'){
  const buscar = document.getElementById(buscarId);
  const anio = document.getElementById(anioId);
  const tipo = document.getElementById(tipoId);
  const limpiar = document.getElementById(limpiarId);
  if (!buscar || !anio || !tipo || !limpiar) return;

  const anios = [...new Set(PUBLICACIONES.map(p => p.year))].sort((a,b)=>b-a);
  anio.innerHTML = '<option value="">Todos los años</option>' + anios.map(y=>`<option value="${y}">${y}</option>`).join('');

  const filtrar = () => {
    const q = buscar.value.toLowerCase();
    const y = anio.value;
    const t = tipo.value;
    publicacionesFiltradasPT = PUBLICACIONES.filter(p =>
      (!y || p.year == y) &&
      (!t || p.tipo === t) &&
      ((p.titulo + ' ' + (p.tituloIngles || '')).toLowerCase().includes(q))
    );
    paginaActualPT = 1;
    renderPublicacionesPT();
  };

  buscar.addEventListener('input', filtrar);
  anio.addEventListener('change', filtrar);
  tipo.addEventListener('change', filtrar);
  limpiar.addEventListener('click', () => { buscar.value=''; anio.value=''; tipo.value=''; filtrar(); });
}

document.addEventListener('DOMContentLoaded', () => { setupFiltrosPT(); renderPublicacionesPT(); });
