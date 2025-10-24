function dateFormat(fechaISO: string) {
    const fechaCorta = fechaISO.substring(0, 10);
    const [year, month, day] = fechaCorta.split('-');

    return `${day}/${month}/${year}`;
}

function hourFormat(fecha: string) {
    const dateTimeString: string = fecha;
    const date = new Date(dateTimeString);

    // Obtener la hora en formato 'HH:MM:SS'
    const hours: string = String(date.getHours()).padStart(2, '0');
    const minutes: string = String(date.getMinutes()).padStart(2, '0');
    const seconds: string = String(date.getSeconds()).padStart(2, '0');
    const formattedTime: string = `${hours}:${minutes}:${seconds}`;

    return formattedTime;
}

function datetimeFormat(fechaISO: string) {
    const fecha = new Date(fechaISO);

    return `${fecha.toLocaleTimeString([], { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}`;
}

function humanizeDate(dateString: string) {
    const date = new Date(dateString);
    const now = new Date();

    const minutesDiff = Math.floor((now.getTime() - date.getTime()) / 60000);

    // Ajustar las horas para comparar solo las fechas
    const today = new Date(now.setHours(0, 0, 0, 0));
    const yesterday = new Date(today);

    yesterday.setDate(today.getDate() - 1);

    if (minutesDiff < 1) {
        return 'Hace unos segundos...';
    }

    if (minutesDiff < 60) {
        return `hace ${minutesDiff} min...`;
    } else if (date >= today) {
        return `hoy a las ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else if (date >= yesterday) {
        return `ayer a las ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    } else {
        const formattedDate = date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long' });

        if (date.getFullYear() !== now.getFullYear()) {
            return `${formattedDate} de ${date.getFullYear()} a las ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
        }

        return `${formattedDate} a las ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
    }
}

export {
    dateFormat,
    hourFormat,
    datetimeFormat,
    humanizeDate
}
