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
  },
  { titulo: "Funcionalidad de las cáscaras de la tuna Roja Lisa Parte II, in vivo", year: 2023, tipo: "pdf", portada: "imagenes/FT121 MHer.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=169&t=1" },
  { titulo: "Efecto antidiabético de tallarines con harina extruida de cotiledones de frijol", year: 2023, tipo: "pdf", portada: "imagenes/FT120 RCruz.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=168&t=1" },
  { titulo: "Tecnología para la producción de maíz en condiciones de temporal en Zacatecas", year: 2022, tipo: "pdf", portada: "imagenes/FP46 RSan.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=165&t=1" },
  { titulo: "Estadísticas climatológicas horarias del Estado de Zacatecas (2002–2022)", year: 2021, tipo: "pdf", portada: "imagenes/estClima_Zac.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=164&t=1" },
  { titulo: "Fertilizantes orgánicos y su impacto en la producción de cebada", year: 2020, tipo: "pdf", portada: "imagenes/FT99.png", url: "#" },
  { titulo: "Video: Innovación agrícola 2024", year: 2024, tipo: "video", portada: "imagenes/icopdf.png", url: "https://www.youtube.com/watch?v=tvSlzzXpZEQ" },
  { titulo: "Guía técnica para producción de nopal en Zacatecas", year: 2019, tipo: "pdf", portada: "imagenes/Folleto Tecnico 83.png", url: "#" },
  { titulo: "Manejo sustentable de recursos hídricos", year: 2018, tipo: "pdf", portada: "imagenes/Folleto Tecnico 70.png", url: "#" },
  { titulo: "Manual de prácticas de conservación de suelo", year: 2017, tipo: "pdf", portada: "imagenes/Folleto Tecnico 59.png", url: "#" },
  { titulo: "Evaluación de cultivos alternativos para zonas áridas", year: 2016, tipo: "pdf", portada: "imagenes/Folleto Tecnico 45.png", url: "#" },
  { titulo: "Potencial Productivo de Especies Forrajeras en Zacatecas", year: 2001, tipo: "pdf", portada: "imagenes/PPForrajes.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=1&t=1" },
  { titulo: "Potencial Productivo de Especies Agricolas en Zacatecas", year: 2003, tipo: "pdf", portada: "imagenes/PPAgricolas.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=2&t=1" },
  { titulo: "La siembra en surcos y corrugaciones con pileteo", year: 2004, tipo: "pdf", portada: "imagenes/portadita_siembra_en_surcos.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=3&t=1" },
  { titulo: "Estadísticas Climatológicas Básicas del Estado de Zacatecas. (Período 1961-2003)", year: 2004, tipo: "pdf", portada: "imagenes/Portadita_2.jpg ", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=4&t=1" },
  { titulo: "Red de Monitoreo Agroclimático del Estado de Zacatecas", year: 2005, tipo: "pdf", portada: "imagenes/Portadita.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=5&t=1" },
  { titulo: "Prácticas culturales para producir Durazno Criollo en Zacatecas", year: 2005, tipo: "pdf", portada: "imagenes/duraznoGIF.gif", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=6&t=1" },
  { titulo: "Modificación de la floración, maduración y época de cosecha de nopal tunero", year: 2006, tipo: "pdf", portada: "imagenes/Portadita-floracion-nopal.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=7&t=1" },
  { titulo: "SEQUÍA: Vulnerabilidad impacto y tecnología para afrontarla en el Norte Centro de México 2a Ed", year: 2006, tipo: "pdf", portada: "imagenes/SEQUIA_2aEd.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=8&t=1" },
  { titulo: "Degradación física de los suelos de pastizales bajo pastoreo continuo en el Altiplano de Zacatecas", year: 2007, tipo: "pdf", portada: "imagenes/Degradacion_fisica_de_los_suelos.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=9&t=1" },
  { titulo: "Cadena de sistemas agroalimentarios de chile seco, durazno y frijol en el Estado de Zacatecas", year: 2004, tipo: "pdf", portada: "imagenes/Cadenas_Zacatecas.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=10&t=1" },
  { titulo: "Despunte de ramas mixtas y raleo de fruta en durazno \"Victoria\"", year: 2007, tipo: "pdf", portada: "imagenes/duraznopoda.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=11&t=1" },
  { titulo: "Tecnologia de produccion de Chile Seco", year: 2006, tipo: "pdf", portada: "imagenes/Tecnologia_de_produccion_de_chile_seco.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=12&t=1" },
  { titulo: "Potencial productivo de especies agrícolas en el distrito de desarrollo rural Río Grande, Zacatecas", year: 2007, tipo: "pdf", portada: "imagenes/Potencial_Productivo_de_Especies_Agricolas_DDR_Rio_Grande.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=13&t=1" },
  { titulo: "Uso de estaciones meteorológicas en la agricultura", year: 2008, tipo: "pdf", portada: "imagenes/uso_de_estaciones.gif", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=14&t=1" },
  { titulo: "Veza común y Lathyrus sativus L: Alternativas para producir forraje en Zacatecas", year: 2007, tipo: "pdf", portada: "imagenes/Veza-lathyrus.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=15&t=1" },
  { titulo: "Probabilidad de ocurrencia de heladas en el estado de Zacatecas", year: 2008, tipo: "pdf", portada: "imagenes/heladas.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=16&t=1" },
  { titulo: "Riego parcial de la raíz: Una alternativa para mejorar la productividad y ahorro de agua en manzano", year: 2009, tipo: "pdf", portada: "imagenes/rprManz.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=17&t=1" },
  { titulo: "Potencial Productivo de especies Agricolas en el distrito de desarrollo rural Zacatecas, Zacatecas.", year: 2009, tipo: "pdf", portada: "imagenes/potZac.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=18&t=1" },
  { titulo: "Diagnóstico de los recursos naturales para la planeación de la investigación tecnológica y el ordenamiento ecológico", year: 2009, tipo: "pdf", portada: "imagenes/diagRecursosN.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=19&t=1" },
  { titulo: "Tecnología para cultivar ajo en zacatecas", year: 2009, tipo: "pdf", portada: "imagenes/tecnoAjo.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=20&t=1" },
  { titulo: "Recomendaciones para el manejo de agalla de la Corona y enfermedades virales de la vid en Zacatecas", year: 2009, tipo: "pdf", portada: "imagenes/folletoVid.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=21&t=1" },
  { titulo: "El virus de la marchitez manchada del jitomate", year: 2009, tipo: "pdf", portada: "imagenes/mJito.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=22&t=1" },
  { titulo: "Produccion de plantula de chile en invernadero", year: 2010, tipo: "pdf", portada: "imagenes/pantulaChila.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=23&t=1" },
  { titulo: "El Virus de la mancha amarilla del iris", year: 2010, tipo: "pdf", portada: "imagenes/virusIris.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=24&t=1" },
  { titulo: "Descripcion Fenotipica de material genetico de Durazno para Zacatecas", year: 2009, tipo: "pdf", portada: "imagenes/portadaD.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=25&t=1" },
  { titulo: "Enfermedades bóticas del ajo y chile en Aguascalientes y Zacatecas ", year: 2009, tipo: "pdf", portada: "imagenes/Enfermedades de Ajo y Chile.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=26&t=1" },
  { titulo: "Evaluación del impacto económico, social y ambiental del proyecto manejo integral de huertos de durazno en el estado de Zacatecas.", year: 2010, tipo: "pdf", portada: "imagenes/Huertosmodelos.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=27&t=1" },
  { titulo: "Manejo Integrado de plagas y enfermedades de frijol en Zacatecas", year: 2010, tipo: "pdf", portada: "imagenes/PlagasFrijol.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=28&t=1" },
  { titulo: "Evaluación del entorno para la innovación tecnológica en zacatecas: identificación de las cadenas productivas relevantes", year: 2010, tipo: "pdf", portada: "imagenes/Cadenasproductivas.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=29&t=1" },
  { titulo: "Enfermedades provocadas por virus en el cultivo de ajo en el norte centro de México", year: 2010, tipo: "pdf", portada: "imagenes/Enfermedadesajo.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=30&t=1" },
  { titulo: "Factores que influye en la vida de anaquel de la TUNA (Opuntia spp.) Un estudio exploratorio", year: 2010, tipo: "pdf", portada: "imagenes/folletoTuna.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=31&t=1" },
  { titulo: "Produccion y ensilaje de Maiz Forrajero de riego", year: 2010, tipo: "pdf", portada: "imagenes/folletoMaiz.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=32&t=1" },
  { titulo: "Guia para la produccion de CANOLA en Zacatecas", year: 2010, tipo: "pdf", portada: "imagenes/PortadaCanola.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=33&t=1" },
  { titulo: "Virus de frijol en la Comarca Lagunera y Zacatecas", year: 2010, tipo: "pdf", portada: "imagenes/portadaVFrijol.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=34&t=1" },
  { titulo: "Botana a base de frijol con alto valor nutricional y nutracéutico", year: 2010, tipo: "pdf", portada: "imagenes/portadaFrijol.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=35&t=1" },
  { titulo: "Adelanto de cosecha e incremento de rendimiento en chile tipo Ancho mediante trasplante de plántulas de edad avanzada", year: 2011, tipo: "pdf", portada: "imagenes/rendimientoChile.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=37&t=1" },
  { titulo: "CEZAC 06: Variedad de ajo jaspeado para la región norte centro de México", year: 2011, tipo: "pdf", portada: "imagenes/cezac06ajo.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=38&t=1" },
  { titulo: "Situación actual y agenda de trabajo para la innovación tecnológica del sistema producto vid en Zacatecas", year: 2010, tipo: "pdf", portada: "imagenes/vidActual.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=39&t=1" },
  { titulo: "Producción de panqué y barritas, alimentos de la panificación preparados con harina compuesta de frijol, trigo y avena", year: 2011, tipo: "pdf", portada: "imagenes/panqueYBarritas.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=40&t=1" },
  { titulo: "Manual elaboración de productos agroindustriales de frijol", year: 2011, tipo: "pdf", portada: "imagenes/manualFrigol.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=41&t=1" },
  { titulo: "Aplicación de envolturas comestibles a base de Mucílago de Nopal para extender la vidad de anaquel de frutas perecederas", year: 2012, tipo: "pdf", portada: "imagenes/mdeNopal.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=42&t=1" },
  { titulo: "Proceso de la Elaboración de Dulce de Tuna", year: 2010, tipo: "pdf", portada: "imagenes/procElaDTuna.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=43&t=1" },
  { titulo: "La Jatropha (Jatropha curcas L.) en Zacatecas", year: 2010, tipo: "pdf", portada: "imagenes/Jatropha.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=44&t=1" },
  { titulo: "Adopción de la tecnología “siembra en surcos doble hilera y pileteo” en cebada maltera en el estado de Zacatecas. Un análisis del proceso y los impactos.", year: 2011, tipo: "pdf", portada: "imagenes/AdoSurcoDH.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=45&t=1" },
  { titulo: "Licor de Durazno.", year: 2011, tipo: "pdf", portada: "imagenes/licDurazno.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=46&t=1" },
  { titulo: "Ate de Durazno.", year: 2011, tipo: "pdf", portada: "imagenes/AteDurazno.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=47&t=1" },
  { titulo: "Extracción y purificación de mucilago de nopal.", year: 2011, tipo: "pdf", portada: "imagenes/extMuNopal.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=48&t=1" },
  { titulo: "Orejon de durazno deshidratados con energía solar.", year: 2011, tipo: "pdf", portada: "imagenes/oreDurazno.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=49&t=1" },
  { titulo: "Técnicas para la transformación de leche de cabra en zonas marginales.", year: 2011, tipo: "pdf", portada: "imagenes/tecLecheCabra.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=50&t=1" },
  { titulo: "La interacción de un macho cabrío sexualmente activo con un tratamiento fotoperiódico reduce la estacionalidad en cabras anestricas.", year: 2011, tipo: "pdf", portada: "imagenes/machoCabrio.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=51&t=1" },
  { titulo: "Producción de forraje con cereales de grano pequeño.", year: 2011, tipo: "pdf", portada: "imagenes/procForrCP.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=52&t=1" },
  { titulo: "Amarillamientos del chile para secado.", year: 2011, tipo: "pdf", portada: "imagenes/amaChileSecado.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=53&t=1" },
  { titulo: "Ecología del hongo causante de la pudrición blanca.", year: 2011, tipo: "pdf", portada: "imagenes/ecoHongo.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=54&t=1" },
  { titulo: "Uso de zeolita para captura de nitrógeno en estiércol bovino", year: 2012, tipo: "pdf", portada: "imagenes/usoZeolita.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=55&t=1" },
  { titulo: "Producción de plántula de chile en invernadero: Manual para el productor.", year: 2012, tipo: "pdf", portada: "imagenes/plantulaChileMa.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=56&t=1" },
  { titulo: "Metodología para el diseño, aplicación y análisis de encuestas sobre adopción de tecnologías en productores rurales.", year: 2012, tipo: "pdf", portada: "imagenes/encuestasRurales.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=57&t=1" },
  { titulo: "Manejo de enfermedades virales de ajo en Zacatecas. ", year: 2012, tipo: "pdf", portada: "imagenes/viralesAjo.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=58&t=1" },
  { titulo: "Producción de chile seco con riego por goteo sub-superficial.", year: 2012, tipo: "pdf", portada: "imagenes/prodChileSe.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=59&t=1" },
  { titulo: "Manejo de plantaciones de nopal tunero en el Altiplano Potosino", year: 2012, tipo: "pdf", portada: "imagenes/planNopTun.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=60&t=1" },
  { titulo: "Producción y comercialización del durazno criollo  de Zacatecas.", year: 2012, tipo: "pdf", portada: "imagenes/proComDurCriollo.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=61&t=1" },
  { titulo: "Avances de investigación sobre corteza corchosa–madera rugosa de vid en Aguascalientes.", year: 2010, tipo: "pdf", portada: "imagenes/VidAgus.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=64&t=1" },
  { titulo: "Sistema en línea para programación de riego de chile y frijol en Zacatecas.", year: 2012, tipo: "pdf", portada: "imagenes/sisRieg.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=65&t=1" },
  { titulo: "Sistema de alerta para conchuela del frijol y gusano cogollero en el estado de Zacatecas.", year: 2012, tipo: "pdf", portada: "imagenes/sisPlaga.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=66&t=1" },
  { titulo: "Alimentación y manejo de bovinos en agostadero durante épocas de sequía.", year: 2012, tipo: "pdf", portada: "imagenes/alimmase.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=67&t=1" },
  { titulo: "Prácticas de restauración de suelos para la conservación del agua.", year: 2012, tipo: "pdf", portada: "imagenes/ressuel.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=68&t=1" },
  { titulo: "Bancos de proteína para rumiantes en el Semiárido Mexicano", year: 2012, tipo: "pdf", portada: "imagenes/bancpro.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=69&t=1" },
  { titulo: "Carga animal del pastizal mediano abierto en zacatecas  (Segundo trimestre del 2007)", year: 2007, tipo: "pdf", portada: "imagenes/caan2t.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=70&t=1" },
  { titulo: "Carga animal del pastizal mediano abierto en zacatecas  (Tercer trimestre del 2007)", year: 2007, tipo: "pdf", portada: "imagenes/caan3t.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=71&t=1" },
  { titulo: "Carga animal del pastizal mediano abierto en zacatecas  (Cuarto trimestre del 2007)", year: 2007, tipo: "pdf", portada: "imagenes/caan4t.jpg", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=72&t=1" },
  { titulo: "Manejo de las principales enfermedades del chile para secado en el norte centro de México.", year: 2013, tipo: "pdf", portada: "imagenes/EnfChilS.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=73&t=1" },
  { titulo: "Selección y conservación de semilla de chile: primer paso para una buena cosecha.", year: 2013, tipo: "pdf", portada: "imagenes/semillaCH.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=74&t=1" },
  { titulo: "Virus y fitoplasmas asociados con el cultivo de chile para secado en el norte centro de México.", year: 2013, tipo: "pdf", portada: "imagenes/VFcultivoCh.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=75&t=1" },
  { titulo: "Presencia y manejo de los virus hoja de abanico y enrollamiento de la hoja en viñedos de Aguascalientes.", year: 2013, tipo: "pdf", portada: "imagenes/vhojaA.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=76&t=1" },
  { titulo: "Control de Malezas con Escardas y Herbicidas Preemergentes en Frijol en Zacatecas.", year: 2004, tipo: "pdf", portada: "imagenes/Control_de_Malezas.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=77&t=1" },
  { titulo: "Barretero, variedad de ajo jaspeado para Zacatecas.", year: 2014, tipo: "pdf", portada: "imagenes/barretero.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=78&t=1" },
  { titulo: "Desgranadora de ajo para pequeños productores.", year: 2014, tipo: "pdf", portada: "imagenes/desgranadoAjo.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=79&t=1" },
  { titulo: "Guía para producción de cebolla en Zacatecas.", year: 2014, tipo: "pdf", portada: "imagenes/prodCebolla.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=80&t=1" },
  { titulo: "Microsilos: Una alternativa para pequeños productores.", year: 2014, tipo: "pdf", portada: "imagenes/microsilos.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=81&t=1" },
  { titulo: "Nuevas variedades de frijol para el estado de Zacatecas.", year: 2014, tipo: "pdf", portada: "imagenes/nuevaVariedadFrijol.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=82&t=1" },
  { titulo: "Prácticas agronómicas para mejorar el suelo cultivado con chile Mirasol.", year: 2014, tipo: "pdf", portada: "imagenes/mejoraSuelo.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=83&t=1" },
  { titulo: "Producción de semilla de frijol.", year: 2014, tipo: "pdf", portada: "imagenes/produccionSemillaFrijol.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=84&t=1" },
  { titulo: "Selección y almacenamiento de semilla de frijol.", year: 2014, tipo: "pdf", portada: "imagenes/almacenamientoFrijol.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=85&t=1" },
  { titulo: "Tipificación fisicoquímica y productos agroindustriales de ajos zacatecanos.", year: 2014, tipo: "pdf", portada: "imagenes/fisicoquimica.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=86&t=1" },
  { titulo: "Manejo de enfermedades de los almácigos tradicionales de chile para secado en Zacatecas.", year: 2014, tipo: "pdf", portada: "imagenes/manejoEAlmacigosChile.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=87&t=1" },
  { titulo: "Fitoplasmas: Otros agentes fitopatógenos.", year: 2014, tipo: "pdf", portada: "imagenes/aFitopatogenos.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=88&t=1" },
  { titulo: "Incidencia de enfermedades parasitarias de chile en el norte centro de México.", year: 2014, tipo: "pdf", portada: "imagenes/eParaChile.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=89&t=1" },
  { titulo: "Virus y fitoplasmas de chile: Una perspectiva regional.", year: 2014, tipo: "pdf", portada: "imagenes/viFitoChileRegional.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=90&t=1" },
  { titulo: "Variedades de Manzana recomendadas para las serranías de Hidalgo y Querétaro.", year: 2010, tipo: "pdf", portada: "imagenes/vManzHQ.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=91&t=1" },
  { titulo: "Principales cultivares mexicanos de nopal tunero.", year: 2000, tipo: "pdf", portada: "imagenes/pCultMNTuna.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=92&t=1" },
  { titulo: "Variedades mejoradas y selecciones de Durazno del INIFAP.", year: 2011, tipo: "pdf", portada: "imagenes/varDurMInifap.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=93&t=1" },
  { titulo: "Cultivo del chile en México, Tendencias de producción y problemas fitosanitarios actuales.", year: 2012, tipo: "pdf", portada: "imagenes/cultChprofit.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=94&t=1" },
  { titulo: "Comportamiento Agronómico del Pasto Banderilla [Bouteloua curtipendula (Michx.) Torr.] en el Altiplano de Zacatecas.", year: 2015, tipo: "pdf", portada: "imagenes/banderilla.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=95&t=1" },
  { titulo: "Sistema de producción de forrajes de temporal una opción para la reconversión productiva", year: 2014, tipo: "pdf", portada: "imagenes/sproduccion14.png", url: "http://zacatecas.inifap.gob.mx/modulo/mostrarPub.php?id=96&t=1" },
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
const anios = [...new Set(publicaciones.map(p => p.year))].sort((a,b)=>b-a);
anio.innerHTML = '<option value="">Todos los años</option>' + anios.map(y => `<option value="${y}">${y}</option>`).join('');

// Renderizado
function renderPublicaciones() {
  contenedor.innerHTML = '';
  const inicio = (paginaActual - 1) * porPagina;
  const fin = inicio + porPagina;
  const visibles = publicacionesFiltradas.slice(inicio, fin);

  visibles.forEach(p => {
    const card = document.createElement('div');
    card.className = 'col-md-4';
    card.innerHTML = `
      <div class="card card-publicacion h-100">
        <img src="${p.portada}" class="card-img-top" alt="${p.titulo}">
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
