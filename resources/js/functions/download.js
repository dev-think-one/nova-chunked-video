

export default function (resourceName, resourceId, attribute) {
    const link = document.createElement('a');
    link.href = `/nova-api/${resourceName}/${resourceId}/download/${attribute}`;
    link.download = 'download';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
