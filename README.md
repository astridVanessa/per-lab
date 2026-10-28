# Laboratorio PER: desarrollo seguro y DevSecOps

Laboratorio del proyecto final de Seguridad en Aplicaciones (Opción B: e-commerce cloud-native,
con Amazon.com como referencia). Reproduce, fuera de Magento, la lógica de cinco hallazgos de la
matriz de riesgos inicial (R-05, R-06, R-08, R-09 y R-10) y su corrección.

## Estructura

| Carpeta | Contenido |
|---|---|
| `antes/` | Código vulnerable, solo para demostrar los hallazgos. No se despliega. |
| `src/` | Código corregido. Es lo que analiza el pipeline. |
| `tests/pruebas_abuso.php` | 7 pruebas de abuso y 1 control funcional. |
| `.semgrep/per-reglas.yml` | 5 reglas SAST propias ligadas a los hallazgos de la Fase I. |
| `frontend-antes/`, `frontend/` | Dependencias JavaScript del tema, desactualizadas y actualizadas. |
| `.github/workflows/devsecops.yml` | Pipeline de GitHub Actions: SAST, SCA, pruebas y compuerta. |
| `scripts/pipeline_local.sh` | Reproduce las compuertas del pipeline en una computadora. |
| `infra/iam/` | Política de permisos y de confianza del rol `rol-magento-app`. |
| `vendor-lab/php-jwt/` | Copia de firebase/php-jwt 6.10.0 (licencia BSD-3) para correr las pruebas sin Composer. |

## Requisitos

PHP 8.1 o superior (con pdo_sqlite y openssl), Node.js 20 o superior y Semgrep
(`pip install semgrep`).

## Cómo ejecutarlo

```bash
# Código vulnerable: las tres compuertas deben bloquear
scripts/pipeline_local.sh antes frontend-antes

# Código corregido: las tres compuertas deben aprobar
scripts/pipeline_local.sh src frontend
```

## Ejecutarlo en GitHub Actions

1. Crear un repositorio en GitHub y subir esta carpeta.
2. Crear el secreto `NVD_API_KEY` (llave gratuita de https://nvd.nist.gov/developers/request-an-api-key)
   para OWASP Dependency-Check.
3. Abrir un pull request hacia `main`. El pipeline corre solo y guarda los reportes como artefactos.

El código de `antes/` es intencionalmente inseguro. No debe copiarse a un sistema real.
