export default function getUserString(n) {
	return `${n.last || 'unknown'}, ${n.first || 'name'}${n.mi ? ' ' + n.mi + '.' : ''}`
}
