import { usePage } from "@inertiajs/vue3";
import { can, role } from "@/helpers/permissionHelper";
import { PageProps } from "@/types/UsePage";
import { Reporte } from "@/types/Reporte";
import { EstatusReporteEnum } from "@/enums/estatus-reporte.enum";
import { RoleEnum } from "@/enums/role.enum";
import { RegistroReporteEnum } from "@/enums/registro-reporte.enum";

export class ReportePolicy {
    // Persona del usuario autenticado
    private authPersona = usePage<PageProps>().props.auth.persona;

    // Método para determinar si se puede editar un reporte
    public canUpdateReporte = (reporte: Reporte) => {
        if (role(RoleEnum.DESARROLLADOR)) {
            return true;
        }

        if (! can('reporte.update')) {
            return false;
        }

        if (reporte.estatus !== EstatusReporteEnum.REPORTADO && reporte.estatus !== EstatusReporteEnum.EN_REVISION) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede eliminar un reporte
    public canDeleteReporte = (reporte: Reporte) => {
        if (! can('reporte.delete')) {
            return false;
        }

        if (reporte.estatus !== EstatusReporteEnum.REPORTADO && reporte.estatus !== EstatusReporteEnum.EN_REVISION) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede marcar un reporte como duplicado
    public canMarcarReporteDuplicado = (reporte: Reporte) => {
        if (! can('reporte.manage')) {
            return false;
        }

        if (reporte.registro !== RegistroReporteEnum.UNICO) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede ver la evidencia de un reporte
    public canVerEvidencia = (reporte: Reporte) => {
        if (! can('reporte.show')) {
            return false;
        }

        if (reporte.estatus !== EstatusReporteEnum.ATENDIDO && reporte.estatus !== EstatusReporteEnum.VALIDADO) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede subir evidencia a un reporte
    public canSubirEvidencia = (reporte: Reporte) => {
        if (! can('reporte.manage')) {
            return false;
        }

        if (reporte.estatus !== EstatusReporteEnum.ATENDIDO) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede inciciar la revisión de un reporte
    public canIniciarRevisionReporte = (reporte: Reporte) => {
        if (! can('reporte.manage')) {
            return false;
        }

        if (reporte.estatus !== EstatusReporteEnum.REPORTADO) {
            return false;
        }

        return true;
    }

    // Método para determinar si se puede validar un reporte
    public canValidarReporte = (reporte: Reporte) => {
        if (! can('reporte.create')) {
            return false;
        }

        if (this.authPersona.id !== reporte.solicitante_id || reporte.estatus !== EstatusReporteEnum.ATENDIDO) {
            return false;
        }

        return true;
    }
}
