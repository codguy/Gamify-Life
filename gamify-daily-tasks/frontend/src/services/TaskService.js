// src/services/TaskService.js
import apiClient from './apiClient';

const TaskService = {
  // Fetch all tasks for the current user
  getAllTasks: async () => {
    try {
      const response = await apiClient.get('/tasks');
      // Yii2's ActiveDataProvider, when serialized to JSON without specific configuration,
      // often returns an array of objects directly.
      // If you've configured it to include pagination/sorting meta-data at the root level,
      // you might need to access response.data.items or similar.
      // For now, assuming it's a direct array of task objects.
      return response.data; 
    } catch (error) {
      console.error('Error fetching tasks:', error.response?.data || error.message);
      throw error;
    }
  },

  // Fetch a single task by its ID
  getTaskById: async (taskId) => {
    try {
      const response = await apiClient.get(`/tasks/${taskId}`);
      return response.data;
    } catch (error) {
      console.error(`Error fetching task ${taskId}:`, error.response?.data || error.message);
      throw error;
    }
  },

  // Create a new task
  // taskData should be an object like { title: 'New Task', description: '...', due_date: '...', status: 'pending' }
  createTask: async (taskData) => {
    try {
      const response = await apiClient.post('/tasks', taskData);
      return response.data;
    } catch (error) {
      console.error('Error creating task:', error.response?.data || error.message);
      // Re-throw a more structured error or the message from the backend
      const errorDetail = error.response?.data?.errors || error.response?.data?.message || 'Failed to create task.';
      throw new Error(typeof errorDetail === 'string' ? errorDetail : JSON.stringify(errorDetail));
    }
  },

  // Update an existing task
  // taskData can be a partial object with fields to update
  updateTask: async (taskId, taskData) => {
    try {
      const response = await apiClient.put(`/tasks/${taskId}`, taskData); // Or PATCH for partial updates
      return response.data;
    } catch (error) {
      console.error(`Error updating task ${taskId}:`, error.response?.data || error.message);
      const errorDetail = error.response?.data?.errors || error.response?.data?.message || 'Failed to update task.';
      throw new Error(typeof errorDetail === 'string' ? errorDetail : JSON.stringify(errorDetail));
    }
  },

  // Delete a task by its ID
  deleteTask: async (taskId) => {
    try {
      await apiClient.delete(`/tasks/${taskId}`);
      // DELETE typically returns 204 No Content, so no response.data to return
    } catch (error) {
      console.error(`Error deleting task ${taskId}:`, error.response?.data || error.message);
      const errorDetail = error.response?.data?.message || 'Failed to delete task.'; // DELETE might not have 'errors' field
      throw new Error(errorDetail);
    }
  },
};

export default TaskService;