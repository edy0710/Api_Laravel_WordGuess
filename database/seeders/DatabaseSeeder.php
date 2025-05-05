<?php

// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Word;
use App\Models\Option;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Crear categorías
        $categories = [
            'Tecnología' => 'Términos tecnológicos y de informática',
            'Ciencia' => 'Términos científicos y de investigación',
            'Arte' => 'Términos artísticos y culturales'
        ];

        foreach ($categories as $name => $desc) {
            $category = Category::create([
                'name' => $name,
                'description' => $desc
            ]);

            // Crear palabras para cada categoría
            $words = $this->getWordsForCategory($name);
            
            foreach ($words as $wordData) {
                $word = Word::create([
                    'category_id' => $category->id,
                    'word' => $wordData['word'],
                    'correct_meaning' => $wordData['meaning']
                ]);

                // Crear opciones para cada palabra
                foreach ($wordData['options'] as $optionText) {
                    Option::create([
                        'word_id' => $word->id,
                        'option_text' => $optionText,
                        'is_correct' => $optionText === $wordData['meaning']
                    ]);
                }
            }
        }
    }

    protected function getWordsForCategory($category)
    {
        if ($category === 'Tecnología') {
            return [
                // 30 entradas de tecnología
                [
                    'word' => 'API',
                    'meaning' => 'Interfaz de Programación de Aplicaciones',
                    'options' => [
                        'Interfaz de Programación de Aplicaciones',
                        'Algoritmo de Procesamiento Inteligente',
                        'Aplicación para Internet'
                    ]
                ],
                [
                    'word' => 'Blockchain',
                    'meaning' => 'Cadena de bloques de información segura',
                    'options' => [
                        'Tipo de red inalámbrica',
                        'Cadena de bloques de información segura',
                        'Lenguaje de programación'
                    ]
                ],
                [
                    'word' => 'Cloud Computing',
                    'meaning' => 'Modelo de computación basado en infraestructura virtual a través de internet',
                    'options' => [
                        'Sistema de almacenamiento local de datos',
                        'Modelo de computación basado en infraestructura virtual a través de internet',
                        'Protocolo de comunicación inalámbrica'
                    ]
                ],
                [
                    'word' => 'Machine Learning',
                    'meaning' => 'Subcampo de la inteligencia artificial que permite a los sistemas aprender patrones',
                    'options' => [
                        'Herramienta de diseño gráfico',
                        'Subcampo de la inteligencia artificial que permite a los sistemas aprender patrones',
                        'Técnica de análisis químico'
                    ]
                ],
                [
                    'word' => 'Big Data',
                    'meaning' => 'Manejo de grandes volúmenes de datos',
                    'options' => [
                        'Uso de redes sociales para marketing digital',
                        'Almacenamiento local en dispositivos SSD',
                        'Manejo de grandes volúmenes de datos'
                    ]
                ],
                [
                    'word' => 'DevOps',
                    'meaning' => 'Integración entre desarrollo y operaciones',
                    'options' => [
                        'Herramienta para edición de imágenes',
                        'Integración entre desarrollo y operaciones',
                        'Sistema operativo para dispositivos móviles'
                    ]
                ],
                [
                    'word' => 'API',
                    'meaning' => 'Interfaz para comunicación entre programas',
                    'options' => [
                        'Formato de compresión de archivos',
                        'Red inalámbrica de baja latencia',
                        'Interfaz para comunicación entre programas'
                    ]
                ],
                [
                    'word' => 'Blockchain',
                    'meaning' => 'Registro descentralizado de transacciones',
                    'options' => [
                        'Lenguaje de programación funcional',
                        'Registro descentralizado de transacciones',
                        'Dispositivo para almacenamiento externo'
                    ]
                ],
                [
                    'word' => 'Cloud Computing',
                    'meaning' => 'Servicios informáticos a través de internet',
                    'options' => [
                        'Software para diseño gráfico vectorial',
                        'Servicios informáticos a través de internet',
                        'Protocolo de red local (LAN)'
                    ]
                ],
                [
                    'word' => 'Machine Learning',
                    'meaning' => 'Sistemas que aprenden con datos',
                    'options' => [
                        'Técnica de diseño UX/UI moderno',
                        'Sistemas que aprenden con datos',
                        'Plataforma de mensajería instantánea'
                    ]
                ],
                [
                    'word' => 'Ciberseguridad',
                    'meaning' => 'Protección contra amenazas digitales',
                    'options' => [
                        'Programa de gestión de bases de datos',
                        'Protección contra amenazas digitales',
                        'Red social profesional y corporativa'
                    ]
                ],
                [
                    'word' => 'Frontend',
                    'meaning' => 'Parte visible de una aplicación web',
                    'options' => [
                        'Base de datos no relacional',
                        'Parte visible de una aplicación web',
                        'Sistema de autenticación biométrica'
                    ]
                ],
                [
                    'word' => 'Backend',
                    'meaning' => 'Lógica del servidor y base de datos',
                    'options' => [
                        'Software de edición de video',
                        'Lógica del servidor y base de datos',
                        'Herramienta de diseño de interfaces'
                    ]
                ],
                [
                    'word' => 'Framework',
                    'meaning' => 'Estructura base para desarrollar apps',
                    'options' => [
                        'Editor de texto avanzado',
                        'Estructura base para desarrollar apps',
                        'Dispositivo de entrada/salida de datos'
                    ]
                ],
                [
                    'word' => 'Compilador',
                    'meaning' => 'Convierte código en lenguaje máquina',
                    'options' => [
                        'Herramienta para análisis de tráfico web',
                        'Convierte código en lenguaje máquina',
                        'Protocolo de transferencia segura'
                    ]
                ],
                [
                    'word' => 'Algoritmo',
                    'meaning' => 'Secuencia lógica para resolver un problema',
                    'options' => [
                        'Aplicación para llamadas VoIP',
                        'Secuencia lógica para resolver un problema',
                        'Sistema de control de versiones distribuido'
                    ]
                ],
                [
                    'word' => 'Base de Datos',
                    'meaning' => 'Colección organizada de información',
                    'options' => [
                        'Herramienta de diseño de presentaciones',
                        'Colección organizada de información',
                        'Sistema operativo para servidores'
                    ]
                ],
                [
                    'word' => 'SQL',
                    'meaning' => 'Lenguaje para gestionar bases de datos relacionales',
                    'options' => [
                        'Protocolo de red para compartir archivos',
                        'Lenguaje para gestionar bases de datos relacionales',
                        'Formato estándar para documentos PDF'
                    ]
                ],
                [
                    'word' => 'NoSQL',
                    'meaning' => 'Bases de datos sin estructura fija',
                    'options' => [
                        'Software de edición de audio profesional',
                        'Bases de datos sin estructura fija',
                        'Plataforma de streaming multimedia'
                    ]
                ],
                [
                    'word' => 'Open Source',
                    'meaning' => 'Software con código accesible públicamente',
                    'options' => [
                        'Red social enfocada en contenido educativo',
                        'Software con código accesible públicamente',
                        'Protocolo de conexión remota segura'
                    ]
                ],
                [
                    'word' => 'Servidor',
                    'meaning' => 'Equipo que proporciona recursos a otros equipos',
                    'options' => [
                        'Software para diseño de diagramas UML',
                        'Equipo que proporciona recursos a otros equipos',
                        'Dispositivo de almacenamiento en la nube'
                    ]
                ],
                [
                    'word' => 'Cliente-Servidor',
                    'meaning' => 'Arquitectura donde uno solicita y otro responde',
                    'options' => [
                        'Modelo de negocio basado en suscripción',
                        'Arquitectura donde uno solicita y otro responde',
                        'Formato de imagen comprimido sin pérdida'
                    ]
                ],
                [
                    'word' => 'Latencia',
                    'meaning' => 'Tiempo que tarda un dato en viajar',
                    'options' => [
                        'Sistema de autenticación multifactor',
                        'Tiempo que tarda un dato en viajar',
                        'Herramienta para diseño de wireframes'
                    ]
                ],
                [
                    'word' => 'Firewall',
                    'meaning' => 'Sistema que filtra tráfico de red',
                    'options' => [
                        'Dispositivo para escaneo 3D',
                        'Sistema que filtra tráfico de red',
                        'Aplicación de edición de hojas de cálculo'
                    ]
                ],
                [
                    'word' => 'Proxy',
                    'meaning' => 'Intermediario en conexiones de red',
                    'options' => [
                        'Plataforma de gestión de correo electrónico',
                        'Intermediario en conexiones de red',
                        'Sistema operativo para wearables'
                    ]
                ],
                [
                    'word' => 'HTTPS',
                    'meaning' => 'Protocolo seguro de transferencia web',
                    'options' => [
                        'Formato de archivo comprimido',
                        'Protocolo seguro de transferencia web',
                        'Lenguaje de programación orientado a objetos'
                    ]
                ],
                [
                    'word' => 'VPN',
                    'meaning' => 'Red privada sobre una red pública',
                    'options' => [
                        'Software de gestión de contactos',
                        'Red privada sobre una red pública',
                        'Protocolo de transferencia de archivos locales'
                    ]
                ],
                [
                    'word' => 'DOM',
                    'meaning' => 'Estructura del documento HTML',
                    'options' => [
                        'Sistema de detección de movimiento',
                        'Estructura del documento HTML',
                        'Plataforma de desarrollo móvil híbrida'
                    ]
                ],
                [
                    'word' => 'Git',
                    'meaning' => 'Sistema de control de versiones',
                    'options' => [
                        'Entorno de ejecución para JavaScript',
                        'Sistema de control de versiones',
                        'Protocolo de encriptación de datos'
                    ]
                ],
                [
                'word' => 'Internet of Things',
                    'meaning' => 'Red de dispositivos físicos interconectados que comparten datos',
                    'options' => [
                        'Sistema de seguridad bancaria',
                        'Red de dispositivos físicos interconectados que comparten datos',
                        'Método de cultivo agrícola inteligente'
                    ]
                ],
                // Añade 25 más entradas similares...
            ];
        
            
        } elseif ($category === 'Ciencia') {
            return [
                // 30 entradas de ciencia
                [
                    'word' => 'ADN',
                    'meaning' => 'Ácido desoxirribonucleico',
                    'options' => [
                        'Ácido desoxirribonucleico',
                        'Asociación de Nutrición',
                        'Análisis de Datos Naturales'
                    ]
                ],
                [
                    'word' => 'Fotosíntesis',
                    'meaning' => 'Proceso mediante el cual las plantas convierten luz solar en energía',
                    'options' => [
                        'Proceso de división celular',
                        'Proceso mediante el cual las plantas convierten luz solar en energía',
                        'Método de datación arqueológica'
                    ]
                ],
                [
                    'word' => 'Neutrón',
                    'meaning' => 'Partícula subatómica sin carga eléctrica',
                    'options' => [
                        'Partícula subatómica sin carga eléctrica',
                        'Unidad básica de los carbohidratos',
                        'Teoría sobre la expansión del universo'
                    ]
                ],
                [
                    'word' => 'Ecosistema',
                    'meaning' => 'Sistema formado por un conjunto de organismos vivos y su medio físico',
                    'options' => [
                        'Unidad funcional de vida en Marte',
                        'Sistema formado por un conjunto de organismos vivos y su medio físico',
                        'Instrumento de medición astronómica'
                    ]
                ],
                [
                    'word' => 'Célula',
                    'meaning' => 'Unidad básica estructural y funcional de los seres vivos',
                    'options' => [
                        'Unidad básica estructural y funcional de los seres vivos',
                        'Elemento químico del grupo del carbono',
                        'Técnica de análisis de muestras biológicas'
                    ]
                ],
                [
                    'word' => 'Ósmosis',
                    'meaning' => 'Movimiento de agua a través de una membrana semipermeable',
                    'options' => [
                        'Forma de reproducción asexual',
                        'Movimiento de agua a través de una membrana semipermeable',
                        'Reacción nuclear en estrellas'
                    ]
                ],
                [
                    'word' => 'Gravedad',
                    'meaning' => 'Fuerza que atrae objetos hacia el centro de la Tierra',
                    'options' => [
                        'Fenómeno de cambio climático global',
                        'Fuerza que atrae objetos hacia el centro de la Tierra',
                        'Proceso de fermentación natural'
                    ]
                ],
                [
                    'word' => 'Mitosis',
                    'meaning' => 'División celular para crecimiento y reparación de tejidos',
                    'options' => [
                        'Proceso de respiración celular',
                        'División celular para crecimiento y reparación de tejidos',
                        'Formación de tejido óseo'
                    ]
                ],
                [
                    'word' => 'Protón',
                    'meaning' => 'Partícula subatómica con carga positiva',
                    'options' => [
                        'Unidad básica de lípidos',
                        'Partícula subatómica con carga positiva',
                        'Sustancia química en la fotosíntesis'
                    ]
                ],
                [
                    'word' => 'Electrón',
                    'meaning' => 'Partícula subatómica con carga negativa',
                    'options' => [
                        'Unidad estructural de proteínas',
                        'Partícula subatómica con carga negativa',
                        'Gas noble presente en la atmósfera'
                    ]
                ],
                [
                    'word' => 'Energía',
                    'meaning' => 'Capacidad para realizar trabajo o generar cambios',
                    'options' => [
                        'Unidad de medida de temperatura',
                        'Capacidad para realizar trabajo o generar cambios',
                        'Sistema de defensa del cuerpo humano'
                    ]
                ],
                [
                    'word' => 'Evaporación',
                    'meaning' => 'Cambio de estado de líquido a gas',
                    'options' => [
                        'Aumento de la presión atmosférica',
                        'Cambio de estado de líquido a gas',
                        'Transformación de materia orgánica'
                    ]
                ],
                [
                    'word' => 'Gen',
                    'meaning' => 'Unidad hereditaria que contiene información para rasgos específicos',
                    'options' => [
                        'Herramienta de análisis genético',
                        'Unidad hereditaria que contiene información para rasgos específicos',
                        'Elemento químico artificial'
                    ]
                ],
                [
                    'word' => 'Ecología',
                    'meaning' => 'Estudio de las relaciones entre organismos y su entorno',
                    'options' => [
                        'Ciencia que estudia los planetas',
                        'Estudio de las relaciones entre organismos y su entorno',
                        'Técnica de cultivo agrícola tradicional'
                    ]
                ],
                [
                    'word' => 'Biodiversidad',
                    'meaning' => 'Variedad de especies en un área determinada',
                    'options' => [
                        'Proceso de adaptación al frío',
                        'Variedad de especies en un área determinada',
                        'Cambio en el clima global'
                    ]
                ],
                [
                    'word' => 'Biología',
                    'meaning' => 'Ciencia que estudia la vida y sus procesos',
                    'options' => [
                        'Estudio de reacciones químicas',
                        'Ciencia que estudia la vida y sus procesos',
                        'Disciplina del diseño industrial'
                    ]
                ],
                [
                    'word' => 'Química',
                    'meaning' => 'Ciencia que estudia la composición y propiedades de la materia',
                    'options' => [
                        'Estudio del movimiento de los astros',
                        'Ciencia que estudia la composición y propiedades de la materia',
                        'Técnica de impresión digital'
                    ]
                ],
                [
                    'word' => 'Física',
                    'meaning' => 'Ciencia que estudia las fuerzas y leyes que rigen la materia y energía',
                    'options' => [
                        'Estudio de fenómenos meteorológicos',
                        'Ciencia que estudia las fuerzas y leyes que rigen la materia y energía',
                        'Técnica de análisis de ADN'
                    ]
                ],
                [
                    'word' => 'Virus',
                    'meaning' => 'Agente infeccioso que necesita células huésped para replicarse',
                    'options' => [
                        'Organismo multicelular visible al ojo humano',
                        'Agente infeccioso que necesita células huésped para replicarse',
                        'Tipo de bacteria benéfica'
                    ]
                ],
                [
                    'word' => 'Bacteria',
                    'meaning' => 'Organismo unicelular microscópico que puede ser útil o patógeno',
                    'options' => [
                        'Célula vegetal sin núcleo',
                        'Organismo unicelular microscópico que puede ser útil o patógeno',
                        'Elemento radiactivo natural'
                    ]
                ],
                [
                    'word' => 'Antibiótico',
                    'meaning' => 'Sustancia que combate infecciones bacterianas',
                    'options' => [
                        'Medicamento contra virus',
                        'Sustancia que combate infecciones bacterianas',
                        'Material usado en cirugías'
                    ]
                ],
                [
                    'word' => 'Vacuna',
                    'meaning' => 'Sustancia que previene enfermedades activando el sistema inmunológico',
                    'options' => [
                        'Dispositivo quirúrgico moderno',
                        'Sustancia que previene enfermedades activando el sistema inmunológico',
                        'Técnica de diagnóstico por imágenes'
                    ]
                ],
                [
                    'word' => 'Mutación',
                    'meaning' => 'Cambio en el material genético que puede provocar variaciones',
                    'options' => [
                        'Proceso de curación de tejidos',
                        'Cambio en el material genético que puede provocar variaciones',
                        'Método de análisis de suelos'
                    ]
                ],
                [
                    'word' => 'Hibridación',
                    'meaning' => 'Combinación de genes de dos individuos diferentes',
                    'options' => [
                        'Proceso de separación de sustancias',
                        'Combinación de genes de dos individuos diferentes',
                        'Técnica de medición de masa molecular'
                    ]
                ],
                [
                    'word' => 'Clonación',
                    'meaning' => 'Creación de copias genéticamente idénticas',
                    'options' => [
                        'Proceso de síntesis de vitaminas',
                        'Creación de copias genéticamente idénticas',
                        'Técnica de análisis de sangre'
                    ]
                ]
                // Añade 25 más entradas similares...
            ];
        } else {
            return [
                // 30 entradas de arte
                [
                    'word' => 'Impasto',
                    'meaning' => 'Técnica pictórica con capas gruesas de pintura',
                    'options' => [
                        'Estilo de danza italiana',
                        'Técnica pictórica con capas gruesas de pintura',
                        'Tipo de arcilla para escultura'
                    ]
                ],
                [
                    'word' => 'Surrealismo',
                    'meaning' => 'Movimiento artístico basado en el subconsciente y lo onírico',
                    'options' => [
                        'Técnica de escultura en mármol',
                        'Movimiento artístico basado en el subconsciente y lo onírico',
                        'Estilo arquitectónico medieval'
                    ]
                ],
                [
                    'word' => 'Cubismo',
                    'meaning' => 'Movimiento pictórico que representa objetos mediante formas geométricas',
                    'options' => [
                        'Técnica de tejido textil',
                        'Movimiento pictórico que representa objetos mediante formas geométricas',
                        'Estilo musical contemporáneo'
                    ]
                ],
                [
                    'word' => 'Óleo',
                    'meaning' => 'Técnica pictórica que usa pigmentos mezclados con aceites',
                    'options' => [
                        'Técnica de escultura en metal',
                        'Técnica pictórica que usa pigmentos mezclados con aceites',
                        'Instrumento musical de cuerda'
                    ]
                ],
                [
                    'word' => 'Acuarela',
                    'meaning' => 'Técnica de pintura usando colores diluidos en agua',
                    'options' => [
                        'Método de grabado en relieve',
                        'Técnica de pintura usando colores diluidos en agua',
                        'Estilo de danza clásica'
                    ]
                ],
                [
                    'word' => 'Barroco',
                    'meaning' => 'Estilo artístico del siglo XVII caracterizado por el dramatismo y detalles elaborados',
                    'options' => [
                        'Movimiento literario del siglo XIX',
                        'Estilo artístico del siglo XVII caracterizado por el dramatismo y detalles elaborados',
                        'Técnica de cerámica prehispánica'
                    ]
                ],
                [
                    'word' => 'Realismo',
                    'meaning' => 'Movimiento artístico que busca representar la realidad sin idealizarla',
                    'options' => [
                        'Estilo de música folklórica tradicional',
                        'Movimiento artístico que busca representar la realidad sin idealizarla',
                        'Técnica de impresión digital moderna'
                    ]
                ],
                [
                    'word' => 'Dadaísmo',
                    'meaning' => 'Movimiento artístico que rechazaba la lógica y la estética tradicional',
                    'options' => [
                        'Técnica de bordado decorativo',
                        'Movimiento artístico que rechazaba la lógica y la estética tradicional',
                        'Estilo arquitectónico islámico'
                    ]
                ],
                [
                    'word' => 'Expresionismo',
                    'meaning' => 'Estilo que representa la realidad de forma subjetiva y emocional',
                    'options' => [
                        'Técnica de tejido manual',
                        'Estilo que representa la realidad de forma subjetiva y emocional',
                        'Método de pintura rupestre ancestral'
                    ]
                ],
                [
                    'word' => 'Fauvismo',
                    'meaning' => 'Movimiento pictórico que usaba colores intensos y no naturales',
                    'options' => [
                        'Estilo de canto coral religioso',
                        'Movimiento pictórico que usaba colores intensos y no naturales',
                        'Técnica de tallado en piedra'
                    ]
                ],
                [
                    'word' => 'Gótico',
                    'meaning' => 'Estilo arquitectónico y artístico medieval con arcos apuntados y vitrales',
                    'options' => [
                        'Técnica de grabado en madera',
                        'Estilo arquitectónico y artístico medieval con arcos apuntados y vitrales',
                        'Método de escritura antiguo'
                    ]
                ],
                [
                    'word' => 'Romanticismo',
                    'meaning' => 'Movimiento artístico que resaltaba las emociones y la imaginación',
                    'options' => [
                        'Técnica de tejido con telar',
                        'Movimiento artístico que resaltaba las emociones y la imaginación',
                        'Estilo de cocina gourmet internacional'
                    ]
                ],
                [
                    'word' => 'Renacimiento',
                    'meaning' => 'Período artístico y cultural europeo centrado en el humanismo y realismo',
                    'options' => [
                        'Técnica de iluminación teatral',
                        'Período artístico y cultural europeo centrado en el humanismo y realismo',
                        'Estilo de vestimenta del siglo XX'
                    ]
                ],
                [
                    'word' => 'Punto de cruz',
                    'meaning' => 'Técnica de bordado que utiliza puntos en forma de X',
                    'options' => [
                        'Estilo de pintura abstracta',
                        'Técnica de bordado que utiliza puntos en forma de X',
                        'Movimiento artístico del siglo XX'
                    ]
                ],
                [
                    'word' => 'Escultura',
                    'meaning' => 'Arte de crear obras tridimensionales con diversos materiales',
                    'options' => [
                        'Estilo de danza moderna',
                        'Arte de crear obras tridimensionales con diversos materiales',
                        'Técnica de escritura caligráfica'
                    ]
                ],
                [
                    'word' => 'Grabado',
                    'meaning' => 'Técnica artística que consiste en tallar una superficie para imprimir',
                    'options' => [
                        'Método de conservación de alimentos',
                        'Técnica artística que consiste en tallar una superficie para imprimir',
                        'Estilo de música instrumental'
                    ]
                ],
                [
                    'word' => 'Arte efímero',
                    'meaning' => 'Manifestación artística de corta duración o carácter temporal',
                    'options' => [
                        'Técnica de restauración de cuadros antiguos',
                        'Manifestación artística de corta duración o carácter temporal',
                        'Estilo de diseño industrial'
                    ]
                ],
                [
                    'word' => 'Arte cinético',
                    'meaning' => 'Arte que incorpora movimiento físico como parte esencial',
                    'options' => [
                        'Técnica de pintura al óleo',
                        'Arte que incorpora movimiento físico como parte esencial',
                        'Estilo arquitectónico colonial'
                    ]
                ],
                [
                    'word' => 'Performance',
                    'meaning' => 'Obra artística realizada a través de acciones en tiempo real',
                    'options' => [
                        'Técnica de escultura en bronce',
                        'Obra artística realizada a través de acciones en tiempo real',
                        'Estilo de fotografía digital'
                    ]
                ],
                [
                    'word' => 'Collage',
                    'meaning' => 'Técnica artística que combina fragmentos de distintos materiales',
                    'options' => [
                        'Método de análisis de datos visuales',
                        'Técnica artística que combina fragmentos de distintos materiales',
                        'Estilo de animación digital 3D'
                    ]
                ],
                [
                    'word' => 'Pop Art',
                    'meaning' => 'Movimiento artístico inspirado en la cultura popular y los medios',
                    'options' => [
                        'Técnica de tejido con hilo de oro',
                        'Movimiento artístico inspirado en la cultura popular y los medios',
                        'Estilo de música clásica barroca'
                    ]
                ],
                [
                    'word' => 'Minimalismo',
                    'meaning' => 'Estilo artístico que reduce los elementos a su esencia más simple',
                    'options' => [
                        'Técnica de bordado tradicional',
                        'Estilo artístico que reduce los elementos a su esencia más simple',
                        'Método de enseñanza escolar tradicional'
                    ]
                ],
                [
                    'word' => 'Arte digital',
                    'meaning' => 'Creación artística hecha con herramientas tecnológicas',
                    'options' => [
                        'Estilo de pintura con acuarelas',
                        'Creación artística hecha con herramientas tecnológicas',
                        'Técnica de impresión en serigrafía'
                    ]
                ],
                [
                    'word' => 'Arte conceptual',
                    'meaning' => 'Arte donde la idea es más importante que la obra física',
                    'options' => [
                        'Técnica de grabado en metal',
                        'Arte donde la idea es más importante que la obra física',
                        'Estilo de danza tradicional africana'
                    ]
                ],
                [
                    'word' => 'Trompe l’œil',
                    'meaning' => 'Técnica pictórica que engaña la percepción visual',
                    'options' => [
                        'Estilo de música electrónica',
                        'Técnica pictórica que engaña la percepción visual',
                        'Técnica de tejido con punto inglés'
                    ]
                ]
                // Añade 25 más entradas similares...
            ];
        }
    }
}