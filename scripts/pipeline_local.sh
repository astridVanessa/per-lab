#!/usr/bin/env bash
# Reproduce localmente las compuertas del pipeline sobre una carpeta de código y una de dependencias.
# Uso: scripts/pipeline_local.sh antes frontend-antes   |   scripts/pipeline_local.sh src frontend
COD=${1:-src}; DEP=${2:-frontend}; FALLOS=0
echo "== 1. SAST (Semgrep) sobre $COD/"
semgrep scan --metrics=off --disable-version-check -q --config .semgrep/per-reglas.yml \
  --severity ERROR --error "$COD/" && echo "SAST: APROBADO" || { echo "SAST: BLOQUEADO"; FALLOS=$((FALLOS+1)); }
echo "== 2. SCA (npm audit) sobre $DEP/"
(cd "$DEP" && npm audit --audit-level=high) && echo "SCA: APROBADO" || { echo "SCA: BLOQUEADO"; FALLOS=$((FALLOS+1)); }
echo "== 3. Pruebas de abuso sobre $COD/"
php tests/pruebas_abuso.php "$COD" && echo "PRUEBAS: APROBADO" || { echo "PRUEBAS: BLOQUEADO"; FALLOS=$((FALLOS+1)); }
echo
[ $FALLOS -eq 0 ] && echo "RESULTADO: se permite el despliegue" || echo "RESULTADO: despliegue detenido ($FALLOS compuertas fallidas)"
exit $FALLOS
