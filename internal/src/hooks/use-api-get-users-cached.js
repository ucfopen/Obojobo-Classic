import React from 'react'
import { useQuery } from 'react-query'
import { apiGetUserNames } from '../util/api'
import getUserString from '../util/get-user-string'

// hook used to load / cache users
// provide it with userIDs you need
// and it will return all loaded user objects
// it'll cache every user loaded,
// preventing them from being requested multiple times
const useApiGetUsersCached = neededUserIDs => {
	const [users, setUsers] = React.useState({})

	// filter out any users we already have in the cache
	const usersToLoad = React.useMemo(() => {
		return neededUserIDs.filter(id => !users[id])
	}, [neededUserIDs, users])

	//	load users
	const { isError, data, isFetching } = useQuery(
		['getUserNames', ...usersToLoad],
		apiGetUserNames,
		{
			initialStale: true,
			staleTime: Infinity,
			initialData: [],
			enabled: usersToLoad.length // load only after selectedInstance loads
		}
	)

	// process loaded users
	React.useMemo(() => {
		// add a display string for each user
		const defaultUserName = {
			first: 'Unknown',
			last: 'User',
			mi: ''
		}

		const newUsers = {}
		data.forEach(user => {
			const u = { ...user }
			u.userName = { ...defaultUserName, ...u.userName }
			u.userString = getUserString(u.userName)
			newUsers[u.userID] = u
		})

		// add them to the cache for all loaded users
		setUsers({ ...users, ...newUsers })
	}, [data])

	return { users, isError, isFetching }
}

export default useApiGetUsersCached
