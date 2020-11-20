import { apiGetUserNames } from './api'

const users = {}

export default async userIDs => {
	const usersToFetch = userIDs.filter(userID => !users[userID])

	if (usersToFetch.length > 0) {
		const usersFetched = await apiGetUserNames(usersToFetch)

		usersFetched.forEach(userRecord => {
			users[userRecord.userID] = userRecord
			users[userRecord.userID].userString = `${userRecord.userName.last}, ${
				userRecord.userName.first
			}${userRecord.userName.mi ? ' ' + userRecord.userName.mi + '.' : ''}`
		})
	}

	console.log('getUsers', userIDs, users)

	return userIDs.map(userID => users[userID])
}
